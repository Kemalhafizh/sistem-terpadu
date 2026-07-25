<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Models\QueryPerformanceLog;
use App\Services\Performance\QueryOptimizerService;

class QueryPerformanceController extends Controller
{
    /**
     * Display Query Performance Dashboard.
     */
    public function dashboard(QueryOptimizerService $optimizerService)
    {
        $report = $optimizerService->generateOptimizationReport();
        $logs = QueryPerformanceLog::orderBy('id', 'desc')->take(20)->get();

        return view('performance.query-dashboard', compact('report', 'logs'));
    }

    /**
     * Display Slow Queries list.
     */
    public function slowQueries()
    {
        $slowQueries = QueryPerformanceLog::where('is_slow', true)->orderBy('execution_time_ms', 'desc')->get();

        return view('performance.slow-queries', compact('slowQueries'));
    }

    /**
     * Display Index Optimization Suggestions.
     */
    public function optimizationSuggestions(QueryOptimizerService $optimizerService)
    {
        $suggestions = $optimizerService->suggestIndexes();

        return view('performance.optimization-suggestions', compact('suggestions'));
    }
}
