<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EdwOlapEngineService;
use Illuminate\Http\Request;

class EdwOlapController extends Controller
{
    /**
     * Display Enterprise Data Warehouse & OLAP Aggregation Dashboard.
     */
    public function index(EdwOlapEngineService $olapService)
    {
        $olapSummary = $olapService->getExecutiveOlapSummary();

        return view('admin.analytics.edw', compact('olapSummary'));
    }

    /**
     * Manually trigger refresh of EDW Data Marts.
     */
    public function refreshMarts(Request $request, EdwOlapEngineService $olapService)
    {
        $request->validate([
            'date' => 'nullable|date',
        ]);

        $targetDate = $request->input('date', now()->toDateString());
        $summary = $olapService->refreshAllMarts($targetDate);

        return back()->with('success', "Data Mart EDW (OLAP) untuk tanggal [{$targetDate}] berhasil diperbarui dengan latensi instan!");
    }
}
