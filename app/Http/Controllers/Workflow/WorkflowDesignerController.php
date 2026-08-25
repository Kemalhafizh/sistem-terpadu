<?php

declare(strict_types=1);

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use App\Models\WorkflowBlueprint;
use App\Services\Workflow\VisualWorkflowEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkflowDesignerController extends Controller
{
    public function __construct(
        protected VisualWorkflowEngineService $engineService
    ) {}

    public function index(): View
    {
        $blueprints = WorkflowBlueprint::with(['creator', 'nodes'])->latest()->get();

        return view('workflow-automation.visual-workflow-designer', compact('blueprints'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'process_type' => 'required|in:rkas_approval,letter_signing,leave_request,procurement',
            'bpmn_schema' => 'required|array',
            'blueprint_id' => 'nullable|exists:workflow_blueprints,id',
        ]);

        $blueprint = $this->engineService->saveBlueprint(
            $request->user(),
            $validated,
            $validated['blueprint_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Desain alur kerja BPMN berhasil disimpan.',
            'blueprint' => $blueprint,
        ]);
    }
}
