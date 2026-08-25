<?php

declare(strict_types=1);

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use App\Services\Workflow\ProcessMiningAnalyzerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProcessMiningController extends Controller
{
    public function __construct(
        protected ProcessMiningAnalyzerService $analyzerService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $analysis = $this->analyzerService->analyzeBottlenecks();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'analysis' => $analysis]);
        }

        return view('workflow-automation.process-mining-analyzer', compact('analysis'));
    }
}
