<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Services\Performance\AssetOptimizationService;

class AssetPerformanceController extends Controller
{
    /**
     * Display Core Web Vitals & Asset Optimization Report.
     */
    public function assetReport(AssetOptimizationService $assetService)
    {
        $report = $assetService->generateReport();

        return view('performance.asset-report', compact('report'));
    }
}
