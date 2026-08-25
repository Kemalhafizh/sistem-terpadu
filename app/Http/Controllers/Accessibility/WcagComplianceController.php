<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accessibility;

use App\Http\Controllers\Controller;
use App\Models\WcagScanReport;
use App\Services\Accessibility\WcagComplianceScannerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WcagComplianceController extends Controller
{
    public function __construct(
        protected WcagComplianceScannerService $scannerService
    ) {}

    public function index(): View
    {
        $reports = WcagScanReport::with('issues')->latest()->get();
        if ($reports->isEmpty()) {
            $defaultReport = $this->scannerService->runAutomatedScan(config('app.url', 'http://localhost'));
            $reports = collect([$defaultReport->load('issues')]);
        }

        $latestReport = $reports->first();

        return view('accessibility.wcag-compliance-dashboard', compact('reports', 'latestReport'));
    }

    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => 'required|url',
        ]);

        $report = $this->scannerService->runAutomatedScan($validated['url']);

        return response()->json([
            'success' => true,
            'message' => 'Pemindaian aksesibilitas WCAG 2.2 AA selesai.',
            'report' => $report->load('issues'),
        ]);
    }
}
