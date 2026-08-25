<?php

declare(strict_types=1);

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
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

        $allChains = ApprovalChain::with(['blueprint', 'requester', 'requests.approver'])->latest()->get();
        $blueprints = WorkflowBlueprint::where('is_active', true)->get();

        return view('workflow-automation.document-approval-portal', compact('pendingApprovals', 'allChains', 'blueprints'));
    }

    public function submitDecision(Request $request, ApprovalRequest $approvalRequest): JsonResponse
    {
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
