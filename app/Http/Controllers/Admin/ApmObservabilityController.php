<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ApmTelemetryService;
use Illuminate\Http\Request;

class ApmObservabilityController extends Controller
{
    /**
     * Display Enterprise APM & Distributed Tracing Telemetry Dashboard.
     */
    public function dashboard(ApmTelemetryService $telemetryService)
    {
        $telemetry = $telemetryService->getTelemetryMetrics();
        $slowQueries = $telemetryService->getSlowQueries();

        return view('admin.observability.apm', compact('telemetry', 'slowQueries'));
    }

    /**
     * Simulate Distributed Tracing Lookup by Correlation ID.
     */
    public function simulateTrace(Request $request)
    {
        $correlationId = $request->get('correlation_id', (string) \Illuminate\Support\Str::uuid());

        $spans = [
            [
                'span_id' => 'SPAN-01-HTTP',
                'name' => 'HTTP GET /api/v1/gateway/student/me',
                'service' => 'SISTA API Gateway',
                'duration_ms' => 38.4,
                'status' => 'OK 200',
            ],
            [
                'span_id' => 'SPAN-02-AUTH',
                'name' => 'Sanctum Bearer Token Authentication',
                'service' => 'App\\Http\\Middleware\\RoleBasedRateLimiterMiddleware',
                'duration_ms' => 8.2,
                'status' => 'OK 200',
            ],
            [
                'span_id' => 'SPAN-03-CACHE',
                'name' => 'RequestLifetimeCache Lookup [school_profile]',
                'service' => 'App\\Services\\RequestLifetimeCacheService',
                'duration_ms' => 0.4,
                'status' => 'CACHE HIT (0ms)',
            ],
        ];

        return response()->json([
            'correlation_id' => $correlationId,
            'total_latency_ms' => 47.0,
            'spans' => $spans,
        ]);
    }
}
