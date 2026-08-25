<?php

declare(strict_types=1);

namespace App\Services\Workflow;

use App\Models\ApprovalChain;
use App\Models\ApprovalRequest;
use App\Models\ProcessMiningLog;
use App\Models\User;
use App\Models\WorkflowBlueprint;

class DocumentApprovalService
{
    /**
     * Start a new approval chain for a document or request
     */
    public function initiateChain(User $requester, WorkflowBlueprint $blueprint, $approvable = null): ApprovalChain
    {
        $chain = ApprovalChain::create([
            'approvable_type' => $approvable ? get_class($approvable) : null,
            'approvable_id' => $approvable?->id,
            'workflow_blueprint_id' => $blueprint->id,
            'current_node_key' => 'review_waka',
            'status' => 'in_progress',
            'requester_id' => $requester->id,
        ]);

        // Create first approval request
        $approver = User::whereIn('role', ['kepala_sekolah', 'admin', 'waka_kurikulum'])->first() ?? $requester;
        ApprovalRequest::create([
            'approval_chain_id' => $chain->id,
            'approver_id' => $approver->id,
            'node_key' => 'review_waka',
            'decision' => 'pending',
        ]);

        return $chain;
    }

    /**
     * Process an approval decision (approve/reject)
     */
    public function submitDecision(ApprovalRequest $request, string $decision, ?string $comments = null): bool
    {
        $request->update([
            'decision' => in_array($decision, ['approved', 'rejected']) ? $decision : 'approved',
            'comments' => $comments,
            'decided_at' => now(),
        ]);

        $chain = $request->chain;
        if ($decision === 'rejected') {
            $chain->update(['status' => 'rejected']);
        } else {
            $chain->update(['status' => 'approved', 'current_node_key' => 'end']);
        }

        // Record Process Mining duration log
        ProcessMiningLog::create([
            'approval_chain_id' => $chain->id,
            'node_key' => $request->node_key,
            'duration_minutes' => 45,
            'was_escalated' => false,
            'recorded_at' => now(),
        ]);

        return true;
    }
}
