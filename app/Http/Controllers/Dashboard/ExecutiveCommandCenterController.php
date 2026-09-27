<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\ExecutiveKpiAggregationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExecutiveCommandCenterController extends Controller
{
    public function __construct(
        protected ExecutiveKpiAggregationService $kpiService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $kpis = $this->kpiService->calculateGlobalMetrics();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'kpis' => $kpis]);
        }

        return view('advanced-dashboards.executive-command-center', compact('kpis'));
    }

    public function refresh(): JsonResponse
    {
        $kpis = $this->kpiService->calculateGlobalMetrics();

        return response()->json([
            'success' => true,
            'message' => 'Metrik operasional sekolah berhasil diperbarui secara real-time.',
            'kpis' => $kpis,
        ]);
    }
}
