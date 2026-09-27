<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ObservabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ObservabilityController extends Controller
{
    protected ObservabilityService $service;

    public function __construct(ObservabilityService $service)
    {
        $this->service = $service;
    }

    /**
     * Display the observability dashboard.
     */
    public function dashboard(): View
    {
        $health = $this->service->getSystemHealth();
        $slowRequests = $this->service->getSlowRequests();
        $slowQueries = $this->service->getSlowQueries();
        $errors = $this->service->getErrors();
        $errorCount24h = $this->service->getErrorCount24h();

        return view('admin.observability.dashboard', compact(
            'health',
            'slowRequests',
            'slowQueries',
            'errors',
            'errorCount24h'
        ));
    }

    /**
     * Get system metrics as JSON for real-time polling.
     */
    public function metrics(): JsonResponse
    {
        return response()->json($this->service->getSystemHealth());
    }

    /**
     * Clear all performance and error logs.
     */
    public function clearLogs(): RedirectResponse
    {
        try {
            $logPath = storage_path('logs/observability.json');
            if (file_exists($logPath)) {
                unlink($logPath);
            }

            return redirect()->route('admin.observability.dashboard')
                ->with('success', 'Log observabilitas berhasil dikosongkan.');
        } catch (\Exception $e) {
            return redirect()->route('admin.observability.dashboard')
                ->with('error', 'Gagal mengosongkan log: '.$e->getMessage());
        }
    }

    /**
     * Simulate a slow query or slow request for verification.
     */
    public function simulate(Request $request): RedirectResponse
    {
        $type = $request->input('type', 'request');

        if ($type === 'query') {
            // Run a heavy recursive query to trigger slow query logging (>500ms)
            try {
                DB::select('WITH RECURSIVE cnt(x) AS (SELECT 1 UNION ALL SELECT x+1 FROM cnt LIMIT 4000000) SELECT count(*) FROM cnt;');
            } catch (\Exception $e) {
                // Ignore query exceptions
            }
            $message = 'Simulasi slow query selesai dieksekusi.';
        } else {
            // Sleep for 1.5 seconds to trigger slow request logging (>1000ms)
            usleep(1500000);
            $message = 'Simulasi slow request selesai dieksekusi.';
        }

        return redirect()->route('admin.observability.dashboard')->with('success', $message);
    }
}
