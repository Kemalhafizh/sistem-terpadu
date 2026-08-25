<?php

declare(strict_types=1);

namespace App\Http\Controllers\Workflow;

use App\Http\Controllers\Controller;
use App\Services\Workflow\SlaMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SlaDashboardController extends Controller
{
    public function __construct(
        protected SlaMonitoringService $slaService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $metrics = $this->slaService->getSlaMetrics();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'metrics' => $metrics]);
        }

        return view('workflow-automation.sla-service-dashboard', compact('metrics'));
    }
}
