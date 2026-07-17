<?php

namespace App\Services;

use App\Events\NewNotificationEvent;
use App\Models\User;
use App\Models\WorkflowApproval;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WorkflowService
{
    /**
     * Inisiasi pengajuan approval polimorfik baru.
     */
    public function initiate(string $code, User $requester, Model $requestable, ?string $notes = null): WorkflowRequest
    {
        return DB::transaction(function () use ($code, $requester, $requestable, $notes) {
            $definition = WorkflowDefinition::where('code', $code)->where('is_active', true)->firstOrFail();
            
            $request = WorkflowRequest::create([
                'definition_id' => $definition->id,
                'requester_id' => $requester->id,
                'requestable_type' => get_class($requestable),
                'requestable_id' => $requestable->id,
                'current_step' => 1,
                'status' => 'pending',
                'notes' => $notes,
            ]);

            // Buat baris approval untuk setiap step yang didefinisikan
            foreach ($definition->steps as $step) {
                WorkflowApproval::create([
                    'request_id' => $request->id,
                    'step_order' => $step['order'],
                    'role' => $step['role'],
                    'decision' => 'pending',
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
            if (!$currentApproval) {
                abort(400, 'Langkah persetujuan aktif tidak ditemukan.');
            }

            // Izinkan jika user memiliki role yang sesuai (dan tidak sedang didelegasikan ke orang lain),
            // atau jika user adalah penerima delegasi.
            $isAuthorized = ($currentApproval->role === $approver->role && $currentApproval->delegated_to_id === null)
                || $currentApproval->delegated_to_id === $approver->id;

            if (!$isAuthorized) {
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
                // Maju ke langkah berikutnya
                $request->increment('current_step');
                $request->update(['status' => 'in_review']);
                $request->unsetRelation('currentApproval');
                $this->notifyApprovers($request);
            } else {
                // Semua langkah disetujui -> Persetujuan Selesai!
                $request->update(['status' => 'approved']);
                
                // Perbarui status model induk menjadi approved
                $request->requestable->update([
                    'status' => 'approved',
                    'approved_by' => $approver->id,
                ]);

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
            if (!$currentApproval) {
                abort(400, 'Langkah persetujuan aktif tidak ditemukan.');
            }

            $isAuthorized = ($currentApproval->role === $approver->role && $currentApproval->delegated_to_id === null)
                || $currentApproval->delegated_to_id === $approver->id;

            if (!$isAuthorized) {
                abort(403, 'Anda tidak berwenang menolak pengajuan pada langkah ini.');
            }

            $currentApproval->update([
                'decision' => 'rejected',
                'approver_id' => $approver->id,
                'remarks' => $remarks,
                'decided_at' => now(),
            ]);

            // Hentikan alur dan tandai ditolak
            $request->update(['status' => 'rejected']);
            
            // Perbarui status model induk menjadi ditolak
            $request->requestable->update(['status' => 'rejected']);

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
            if (!$currentApproval) {
                abort(400, 'Langkah persetujuan aktif tidak ditemukan.');
            }

            $isAuthorized = ($currentApproval->role === $approver->role && $currentApproval->delegated_to_id === null)
                || $currentApproval->delegated_to_id === $approver->id;

            if (!$isAuthorized) {
                abort(403, 'Anda tidak berwenang meminta revisi pada langkah ini.');
            }

            $currentApproval->update([
                'decision' => 'revision', // Gunakan status kustom untuk mencatat revisi
                'approver_id' => $approver->id,
                'remarks' => $remarks,
                'decided_at' => now(),
            ]);

            // Set request status ke revision
            $request->update(['status' => 'revision']);
            
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

            // Reset seluruh langkah persetujuan menjadi pending
            foreach ($request->approvals as $approval) {
                $approval->update([
                    'decision' => 'pending',
                    'approver_id' => null,
                    'delegated_to_id' => null,
                    'remarks' => null,
                    'decided_at' => null,
                ]);
            }

            // Kembalikan step ke langkah pertama
            $request->update([
                'current_step' => 1,
                'status' => 'pending',
                'notes' => $notes ?? $request->notes,
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
            if (!$currentApproval) {
                abort(400, 'Langkah persetujuan aktif tidak ditemukan.');
            }

            $isAuthorized = ($currentApproval->role === $approver->role && $currentApproval->delegated_to_id === null)
                || $currentApproval->delegated_to_id === $approver->id;

            if (!$isAuthorized) {
                abort(403, 'Anda tidak berwenang mendelegasikan langkah persetujuan ini.');
            }

            $currentApproval->update([
                'delegated_to_id' => $delegatee->id,
                'remarks' => ($currentApproval->remarks ? $currentApproval->remarks . "\n" : '') . "Didelegasikan dari " . $approver->name . " ke " . $delegatee->name . ($remarks ? ": " . $remarks : ""),
            ]);

            // Kirim notifikasi real-time ke penerima delegasi
            event(new NewNotificationEvent($delegatee, [
                'id' => $request->id,
                'type' => 'workflow_delegation',
                'title' => 'Delegasi Persetujuan: ' . $request->definition->name,
                'description' => $approver->name . ' mendelegasikan persetujuan ' . $request->definition->name . ' milik ' . $request->requester->name . ' kepada Anda.',
                'created_at' => now()->toDateTimeString(),
            ]));
        });
    }

    /**
     * Ambil daftar pengajuan yang sedang menunggu persetujuan pengguna saat ini.
     */
    public function getPendingForUser(User $user)
    {
        return WorkflowRequest::whereIn('status', ['pending', 'in_review'])
            ->whereHas('currentApproval', function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('role', $user->role)
                      ->whereNull('delegated_to_id');
                })->orWhere('delegated_to_id', $user->id);
            })
            ->with(['requester', 'definition', 'approvals'])
            ->latest()
            ->get();
    }

    private function notifyApprovers(WorkflowRequest $request): void
    {
        $currentApproval = $request->currentApproval;
        if (!$currentApproval) return;

        if ($currentApproval->delegated_to_id) {
            $approvers = User::where('id', $currentApproval->delegated_to_id)->get();
        } else {
            $approvers = User::where('role', $currentApproval->role)->get();
        }

        foreach ($approvers as $app) {
            event(new NewNotificationEvent($app, [
                'id' => $request->id,
                'type' => 'workflow_approval',
                'title' => 'Butuh Persetujuan: ' . $request->definition->name,
                'description' => 'Pengajuan dari ' . $request->requester->name . ' menunggu tinjauan Anda.',
                'created_at' => now()->toDateTimeString(),
            ]));
        }
    }

    private function notifyRequester(WorkflowRequest $request, string $action, ?string $remarks = null): void
    {
        event(new NewNotificationEvent($request->requester, [
            'id' => $request->id,
            'type' => 'workflow_status',
            'title' => 'Pengajuan Anda ' . ucfirst($action),
            'description' => 'Pengajuan ' . $request->definition->name . ' telah ' . $action . '.' . ($remarks ? ' Catatan: ' . $remarks : ''),
            'created_at' => now()->toDateTimeString(),
        ]));
    }
}
