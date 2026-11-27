<?php

namespace App\Services;

use App\Events\NewNotificationEvent;
use App\Models\GraduationBatch;
use App\Models\Letter;
use App\Models\PositionAssignment;
use App\Models\StudentMutation;
use App\Models\User;
use App\Models\WorkflowApproval;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowRequest;
use App\Services\Academic\GraduationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WorkflowService
{
    /** @var list<string> */
    private const ESCALATABLE_TO_PRINCIPAL_ROLES = [
        'waka_kurikulum',
        'waka_kesiswaan',
        'waka_sarpras',
        'waka_humas',
        'staf_tu',
        'kepala_tu',
        'koordinator_p5',
        'kepala_perpustakaan',
    ];

    /**
     * Hitung tenggat SLA untuk urutan langkah tertentu berdasarkan konfigurasi definisi alur.
     */
    public function resolveStepDueAt(WorkflowDefinition $definition, int $stepOrder): Carbon
    {
        $slaHours = 48;
        foreach ($definition->steps ?? [] as $step) {
            if ((int) ($step['order'] ?? 0) === $stepOrder) {
                $configured = (int) ($step['sla_hours'] ?? 48);
                $slaHours = $configured > 0 ? $configured : 48;
                break;
            }
        }

        return now()->addHours($slaHours);
    }

    /**
     * Inisiasi pengajuan approval polimorfik baru.
     */
    public function initiate(string $code, User $requester, Model $requestable, ?string $notes = null): WorkflowRequest
    {
        return DB::transaction(function () use ($code, $requester, $requestable, $notes) {
            $definition = WorkflowDefinition::where('code', $code)->where('is_active', true)->firstOrFail();
            $firstStepDueAt = $this->resolveStepDueAt($definition, 1);

            $request = WorkflowRequest::create([
                'definition_id' => $definition->id,
                'requester_id' => $requester->id,
                'requestable_type' => get_class($requestable),
                'requestable_id' => $requestable->id,
                'current_step' => 1,
                'status' => 'pending',
                'notes' => $notes,
                'due_at' => $firstStepDueAt,
            ]);

            // Buat baris approval untuk setiap step yang didefinisikan
            foreach ($definition->steps as $step) {
                $stepOrder = (int) $step['order'];
                WorkflowApproval::create([
                    'request_id' => $request->id,
                    'step_order' => $stepOrder,
                    'role' => $step['role'],
                    'decision' => 'pending',
                    'due_at' => $stepOrder === 1 ? $firstStepDueAt : null,
                ]);
            }

            // Ubah status model induk menjadi 'pending'
            $requestable->update(['status' => 'pending']);

            // Beri notifikasi ke petugas di step pertama
            $this->notifyApprovers($request);

            return $request;
        });
    }

    /**
     * Setujui langkah saat ini.
     */
    public function approve(WorkflowRequest $request, User $approver, ?string $remarks = null): void
    {
        DB::transaction(function () use ($request, $approver, $remarks) {
            $currentApproval = $request->currentApproval;
            if (! $currentApproval) {
                abort(400, 'Langkah persetujuan aktif tidak ditemukan.');
            }

            if ($request->requester_id === $approver->id) {
                abort(403, 'Penyusun/pengaju tidak dapat menyetujui pengajuannya sendiri.');
            }

            // Izinkan jika user memiliki role/jabatan aktif yang sesuai (dan tidak sedang didelegasikan ke orang lain),
            // atau jika user adalah penerima delegasi.
            $isAuthorized = (in_array($currentApproval->role, $approver->effectiveRoles(), true) && $currentApproval->delegated_to_id === null)
                || $currentApproval->delegated_to_id === $approver->id;

            if (! $isAuthorized) {
                abort(403, 'Anda tidak berwenang memberikan persetujuan pada langkah ini.');
            }

            $currentApproval->update([
                'decision' => 'approved',
                'approver_id' => $approver->id,
                'remarks' => $remarks,
                'decided_at' => now(),
            ]);

            $totalSteps = $request->approvals()->count();

            if ($request->current_step < $totalSteps) {
                // Maju ke langkah berikutnya beserta tenggat SLA langkah tersebut
                $nextStepOrder = (int) $request->current_step + 1;
                $request->loadMissing('definition');
                $nextDueAt = $request->definition
                    ? $this->resolveStepDueAt($request->definition, $nextStepOrder)
                    : now()->addHours(48);

                $request->update([
                    'current_step' => $nextStepOrder,
                    'status' => 'in_review',
                    'due_at' => $nextDueAt,
                ]);
                $request->approvals()
                    ->where('step_order', $nextStepOrder)
                    ->update(['due_at' => $nextDueAt]);
                $request->unsetRelation('currentApproval');
                $this->notifyApprovers($request);
            } else {
                // Semua langkah disetujui -> Persetujuan Selesai!
                $request->update([
                    'status' => 'approved',
                    'due_at' => null,
                ]);

                if ($request->requestable instanceof GraduationBatch) {
                    app(GraduationService::class)->onWorkflowApproved($request->requestable, $approver);
                } elseif ($request->requestable instanceof StudentMutation) {
                    app(StudentMutationService::class)->applyApprovedMutation($request->requestable, $approver);
                } else {
                    $requestableUpdates = [
                        'status' => 'approved',
                        'approved_by' => $approver->id,
                    ];
                    if ($request->requestable instanceof Letter) {
                        $requestableUpdates['approved_at'] = now();
                    }
                    $request->requestable->update($requestableUpdates);
                }

                // Beri notifikasi sukses ke pengaju
                $this->notifyRequester($request, 'disetujui');
            }
        });
    }

    /**
     * Tolak pengajuan (Final).
     */
    public function reject(WorkflowRequest $request, User $approver, ?string $remarks = null): void
    {
        DB::transaction(function () use ($request, $approver, $remarks) {
            $currentApproval = $request->currentApproval;
            if (! $currentApproval) {
                abort(400, 'Langkah persetujuan aktif tidak ditemukan.');
            }

            $isAuthorized = (in_array($currentApproval->role, $approver->effectiveRoles(), true) && $currentApproval->delegated_to_id === null)
                || $currentApproval->delegated_to_id === $approver->id;

            if (! $isAuthorized) {
                abort(403, 'Anda tidak berwenang menolak pengajuan pada langkah ini.');
            }

            $currentApproval->update([
                'decision' => 'rejected',
                'approver_id' => $approver->id,
                'remarks' => $remarks,
                'decided_at' => now(),
            ]);

            // Hentikan alur dan tandai ditolak
            $request->update([
                'status' => 'rejected',
                'due_at' => null,
            ]);

            // Perbarui status model induk menjadi ditolak
            $rejectUpdates = ['status' => 'rejected'];
            if ($request->requestable instanceof StudentMutation) {
                $rejectUpdates['rejection_notes'] = $remarks;
            }
            $request->requestable->update($rejectUpdates);

            // Beri notifikasi penolakan ke pengaju
            $this->notifyRequester($request, 'ditolak', $remarks);
        });
    }

    /**
     * Kembalikan pengajuan untuk direvisi (Bukan tolak final).
     */
    public function revise(WorkflowRequest $request, User $approver, string $remarks): void
    {
        DB::transaction(function () use ($request, $approver, $remarks) {
            $currentApproval = $request->currentApproval;
            if (! $currentApproval) {
                abort(400, 'Langkah persetujuan aktif tidak ditemukan.');
            }

            $isAuthorized = (in_array($currentApproval->role, $approver->effectiveRoles(), true) && $currentApproval->delegated_to_id === null)
                || $currentApproval->delegated_to_id === $approver->id;

            if (! $isAuthorized) {
                abort(403, 'Anda tidak berwenang meminta revisi pada langkah ini.');
            }

            $currentApproval->update([
                'decision' => 'revision', // Gunakan status kustom untuk mencatat revisi
                'approver_id' => $approver->id,
                'remarks' => $remarks,
                'decided_at' => now(),
            ]);

            // Set request status ke revision (tidak sedang menunggu penyetuju)
            $request->update([
                'status' => 'revision',
                'due_at' => null,
            ]);

            // Perbarui status model induk menjadi revision
            try {
                $request->requestable->update(['status' => 'revision']);
            } catch (\Exception $e) {
                // Fallback jika enum di model induk tidak mendukung status 'revision'
                $request->requestable->update(['status' => 'pending']);
            }

            // Beri notifikasi revisi ke pengaju
            $this->notifyRequester($request, 'revisi', $remarks);
        });
    }

    /**
     * Kirim ulang pengajuan setelah dilakukan perbaikan revisi.
     */
    public function resubmit(WorkflowRequest $request, User $requester, ?string $notes = null): void
    {
        DB::transaction(function () use ($request, $requester, $notes) {
            if ($request->requester_id !== $requester->id) {
                abort(403, 'Hanya pengaju yang dapat melakukan resubmit.');
            }

            if ($request->status !== 'revision') {
                abort(400, 'Pengajuan tidak sedang dalam status butuh revisi.');
            }

            $request->loadMissing('definition');
            $firstStepDueAt = $request->definition
                ? $this->resolveStepDueAt($request->definition, 1)
                : now()->addHours(48);

            // Reset seluruh langkah persetujuan menjadi pending
            foreach ($request->approvals as $approval) {
                $approval->update([
                    'decision' => 'pending',
                    'approver_id' => null,
                    'delegated_to_id' => null,
                    'remarks' => null,
                    'decided_at' => null,
                    'due_at' => (int) $approval->step_order === 1 ? $firstStepDueAt : null,
                ]);
            }

            // Kembalikan step ke langkah pertama
            $request->update([
                'current_step' => 1,
                'status' => 'pending',
                'notes' => $notes ?? $request->notes,
                'due_at' => $firstStepDueAt,
            ]);

            // Set status model induk kembali ke pending
            $request->requestable->update(['status' => 'pending']);

            // Notifikasi kembali approvers langkah pertama
            $request->unsetRelation('currentApproval');
            $this->notifyApprovers($request);
        });
    }

    /**
     * Delegasikan approval ke pengguna lain.
     */
    public function delegate(WorkflowRequest $request, User $approver, User $delegatee, ?string $remarks = null): void
    {
        DB::transaction(function () use ($request, $approver, $delegatee, $remarks) {
            $currentApproval = $request->currentApproval;
            if (! $currentApproval) {
                abort(400, 'Langkah persetujuan aktif tidak ditemukan.');
            }

            if ((int) $delegatee->id === (int) $request->requester_id) {
                abort(403, 'Persetujuan tidak dapat didelegasikan kepada pemohon alur kerja.');
            }

            $isAuthorized = (in_array($currentApproval->role, $approver->effectiveRoles(), true) && $currentApproval->delegated_to_id === null)
                || $currentApproval->delegated_to_id === $approver->id;

            if (! $isAuthorized) {
                abort(403, 'Anda tidak berwenang mendelegasikan langkah persetujuan ini.');
            }

            $currentApproval->update([
                'delegated_to_id' => $delegatee->id,
                'remarks' => ($currentApproval->remarks ? $currentApproval->remarks."\n" : '').'Didelegasikan dari '.$approver->name.' ke '.$delegatee->name.($remarks ? ': '.$remarks : ''),
            ]);

            // Kirim notifikasi real-time ke penerima delegasi
            event(new NewNotificationEvent($delegatee, [
                'id' => $request->id,
                'type' => 'workflow_delegation',
                'title' => 'Delegasi Persetujuan: '.$request->definition->name,
                'description' => $approver->name.' mendelegasikan persetujuan '.$request->definition->name.' milik '.$request->requester->name.' kepada Anda.',
                'created_at' => now()->toDateTimeString(),
            ]));
        });
    }

    /**
     * Proses pengingat sebelum tenggat SLA dan eskalasi otomatis langkah yang melampaui tenggat (`ALUR_KERJA.md` 1b).
     *
     * @return array{reminded: int, escalated: int}
     */
    public function processSlaAndEscalate(?int $reminderWindowHours = 6): array
    {
        $reminded = 0;
        $escalated = 0;
        $now = now();
        $windowHours = max(1, (int) ($reminderWindowHours ?? 6));

        $upcomingRequests = WorkflowRequest::query()
            ->whereIn('status', ['pending', 'in_review'])
            ->whereNotNull('due_at')
            ->where('due_at', '>', $now)
            ->where('due_at', '<=', $now->copy()->addHours($windowHours))
            ->with(['definition', 'requester', 'approvals'])
            ->get();

        foreach ($upcomingRequests as $req) {
            if (! $req->due_at) {
                continue;
            }
            $cacheKey = sprintf('workflow_sla_reminded:%d:%d:%d', $req->id, $req->current_step, $req->due_at->timestamp);
            if (! Cache::add($cacheKey, true, $now->copy()->addDays(2))) {
                continue;
            }

            $approvers = $this->resolveCurrentApprovers($req);
            foreach ($approvers as $recipient) {
                event(new NewNotificationEvent($recipient, [
                    'id' => $req->id,
                    'type' => 'workflow_sla_reminder',
                    'title' => 'Pengingat Tenggat Persetujuan: '.($req->definition?->name ?? 'Alur Kerja'),
                    'description' => 'Pengajuan dari '.($req->requester?->name ?? '-').' mendekati batas waktu SLA ('.$req->due_at->format('d/m/Y H:i').').',
                    'created_at' => $now->toDateTimeString(),
                ]));
            }

            if ($approvers->isNotEmpty()) {
                $reminded++;
            }
        }

        $overdueRequests = WorkflowRequest::query()
            ->whereIn('status', ['pending', 'in_review'])
            ->whereNotNull('due_at')
            ->where('due_at', '<', $now)
            ->with(['definition', 'requester', 'approvals'])
            ->get();

        foreach ($overdueRequests as $req) {
            $currentApproval = $req->approvals->firstWhere('step_order', (int) $req->current_step);
            if (! $currentApproval || $currentApproval->decision !== 'pending' || $currentApproval->delegated_to_id !== null) {
                continue;
            }

            $fallbackTarget = $this->resolveEscalationTarget($req, (string) $currentApproval->role);
            if ($fallbackTarget !== null) {
                $existingRemarks = $currentApproval->remarks ? $currentApproval->remarks."\n" : '';
                $currentApproval->update([
                    'delegated_to_id' => $fallbackTarget->id,
                    'remarks' => $existingRemarks.'Eskalasi otomatis melewati tenggat SLA ke '.$fallbackTarget->name.'.',
                ]);

                event(new NewNotificationEvent($fallbackTarget, [
                    'id' => $req->id,
                    'type' => 'workflow_sla_escalation',
                    'title' => 'Eskalasi SLA Persetujuan: '.($req->definition?->name ?? 'Alur Kerja'),
                    'description' => 'Langkah persetujuan ('.$currentApproval->role.') melampaui batas waktu SLA dan dialihkan kepada Anda.',
                    'created_at' => $now->toDateTimeString(),
                ]));

                $escalated++;

                continue;
            }

            $alertKey = sprintf('workflow_sla_overdue_alert:%d:%d:%s', $req->id, $req->current_step, $now->toDateString());
            if (Cache::add($alertKey, true, $now->copy()->addDay())) {
                $recipients = $this->resolveCurrentApprovers($req)
                    ->merge(User::where('role', 'admin')->where('id', '!=', $req->requester_id)->get())
                    ->unique('id');

                foreach ($recipients as $recipient) {
                    event(new NewNotificationEvent($recipient, [
                        'id' => $req->id,
                        'type' => 'workflow_sla_overdue',
                        'title' => 'Peringatan SLA Terlampaui: '.($req->definition?->name ?? 'Alur Kerja'),
                        'description' => 'Pengajuan dari '.($req->requester?->name ?? '-').' telah melampaui batas waktu SLA dan menunggu keputusan.',
                        'created_at' => $now->toDateTimeString(),
                    ]));
                }
            }
        }

        return [
            'reminded' => $reminded,
            'escalated' => $escalated,
        ];
    }

    /**
     * Ambil daftar pengajuan yang sedang menunggu persetujuan pengguna saat ini.
     */
    public function getPendingForUser(User $user)
    {
        return WorkflowRequest::awaitingDecisionBy($user)
            ->with(['requester', 'definition', 'approvals'])
            ->latest()
            ->get();
    }

    private function resolveEscalationTarget(WorkflowRequest $request, string $role): ?User
    {
        $actingAssignment = PositionAssignment::query()
            ->activeOn()
            ->where('is_acting', true)
            ->where('user_id', '!=', $request->requester_id)
            ->whereHas('position', fn ($q) => $q->where('code', $role))
            ->orderByDesc('starts_on')
            ->with('user')
            ->first();

        if ($actingAssignment?->user !== null) {
            return $actingAssignment->user;
        }

        if (in_array($role, self::ESCALATABLE_TO_PRINCIPAL_ROLES, true)) {
            $principal = PositionAssignment::holderOn('kepala_sekolah');
            if ($principal !== null && (int) $principal->id !== (int) $request->requester_id) {
                return $principal;
            }

            return User::query()
                ->whereIn('role', ['kepala_sekolah', 'kepsek'])
                ->where('id', '!=', $request->requester_id)
                ->first();
        }

        return null;
    }

    /**
     * @return Collection<int, User>
     */
    private function resolveCurrentApprovers(WorkflowRequest $request)
    {
        $currentApproval = $request->currentApproval;
        if (! $currentApproval) {
            return new Collection;
        }

        if ($currentApproval->delegated_to_id) {
            return User::where('id', $currentApproval->delegated_to_id)
                ->where('id', '!=', $request->requester_id)
                ->get();
        }

        $role = $currentApproval->role;

        return User::query()
            ->where('id', '!=', $request->requester_id)
            ->where(function ($q) use ($role) {
                $q->where('role', $role)
                    ->orWhereHas('positionAssignments', function ($pq) use ($role) {
                        $pq->activeOn()->whereHas('position', fn ($posQ) => $posQ->where('code', $role));
                    });
            })
            ->get();
    }

    private function notifyApprovers(WorkflowRequest $request): void
    {
        $approvers = $this->resolveCurrentApprovers($request);

        foreach ($approvers as $app) {
            event(new NewNotificationEvent($app, [
                'id' => $request->id,
                'type' => 'workflow_approval',
                'title' => 'Butuh Persetujuan: '.$request->definition->name,
                'description' => 'Pengajuan dari '.$request->requester->name.' menunggu tinjauan Anda.',
                'created_at' => now()->toDateTimeString(),
            ]));
        }
    }

    private function notifyRequester(WorkflowRequest $request, string $action, ?string $remarks = null): void
    {
        event(new NewNotificationEvent($request->requester, [
            'id' => $request->id,
            'type' => 'workflow_status',
            'title' => 'Pengajuan Anda '.ucfirst($action),
            'description' => 'Pengajuan '.$request->definition->name.' telah '.$action.'.'.($remarks ? ' Catatan: '.$remarks : ''),
            'created_at' => now()->toDateTimeString(),
        ]));
    }
}
