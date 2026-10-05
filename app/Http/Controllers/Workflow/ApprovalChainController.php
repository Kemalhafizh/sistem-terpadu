<?php

declare(strict_types=1);

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use App\Http\Middleware\RoleMiddleware;
use App\Models\ApprovalChain;
use App\Models\ApprovalRequest;
use App\Models\WorkflowBlueprint;
use App\Services\Workflow\DocumentApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalChainController extends Controller
{
    public function __construct(
        protected DocumentApprovalService $approvalService
    ) {}

    public function index(Request $request): View
    {
        $pendingApprovals = ApprovalRequest::where('approver_id', $request->user()->id)
            ->where('decision', 'pending')
            ->with(['chain.blueprint', 'chain.requester'])
            ->latest()
            ->get();

        // Leadership oversees every chain; others see the chains they started or take part in.
        $user = $request->user();
        $allChains = ApprovalChain::with(['blueprint', 'requester', 'requests.approver'])
            ->when(! in_array($user->role, ['superadmin', 'admin', ...RoleMiddleware::LEADERSHIP_ROLES], true), fn ($q) => $q
                ->where(fn ($q) => $q->where('requester_id', $user->id)->orWhereHas('requests', fn ($r) => $r->where('approver_id', $user->id))))
            ->latest()->get();
        $blueprints = WorkflowBlueprint::where('is_active', true)->get();

        return view('workflow-automation.document-approval-portal', compact('pendingApprovals', 'allChains', 'blueprints'));
    }

    public function submitDecision(Request $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        // Only the approver this step is assigned to may decide it.
        abort_unless((int) $approvalRequest->approver_id === $request->user()->id || $request->user()->role === 'superadmin', 403);

        $validated = $request->validate([
            'decision' => 'required|in:approved,rejected',
            'comments' => 'nullable|string',
        ]);

        $this->approvalService->submitDecision($approvalRequest, $validated['decision'], $validated['comments'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Keputusan persetujuan dokumen berhasil dicatat.',
        ]);
    }
}
