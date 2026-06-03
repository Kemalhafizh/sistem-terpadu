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
            if (!$currentApproval || $currentApproval->role !== $approver->role) {
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
     * Tolak pengajuan.
     */
    public function reject(WorkflowRequest $request, User $approver, ?string $remarks = null): void
    {
        DB::transaction(function () use ($request, $approver, $remarks) {
            $currentApproval = $request->currentApproval;
            if (!$currentApproval || $currentApproval->role !== $approver->role) {
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
            
            // Perbarui status model induk menjadi ditolak/revisi
            $request->requestable->update(['status' => 'rejected']);

            // Beri notifikasi penolakan ke pengaju
            $this->notifyRequester($request, 'ditolak', $remarks);
        });
    }

    /**
     * Ambil daftar pengajuan yang sedang menunggu persetujuan pengguna saat ini.
     */
    public function getPendingForUser(User $user)
    {
        return WorkflowRequest::whereIn('status', ['pending', 'in_review'])
            ->whereHas('currentApproval', function ($query) use ($user) {
                $query->where('role', $user->role);
            })
            ->with(['requester', 'definition', 'approvals'])
            ->latest()
            ->get();
    }

    private function notifyApprovers(WorkflowRequest $request): void
    {
        $currentApproval = $request->currentApproval;
        if (!$currentApproval) return;

        $approvers = User::where('role', $currentApproval->role)->get();
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
