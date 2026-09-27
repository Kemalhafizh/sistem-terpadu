<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ApmTelemetryService;
use App\Services\ObservabilityService;
use App\Services\Observability\RequestMetricsRecorder;
use Illuminate\Http\JsonResponse;
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
     * Look up a real request by its X-Correlation-ID.
     *
     * This used to "simulate" a trace: any id — even one never issued —
     * came back with the same three invented spans and 47 ms total. It now
     * returns what was actually recorded for that id: the request itself
     * (method, path, status, duration) and any slow queries PerformanceLogger
     * captured during it. There is no per-component span timing in this app,
     * so none is shown.
     */
    public function trace(Request $request, RequestMetricsRecorder $recorder, ObservabilityService $observability): JsonResponse
    {
        $validated = $request->validate([
            'correlation_id' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9._-]+$/'],
        ]);
        $correlationId = $validated['correlation_id'];

        $requests = collect($recorder->findByCorrelationId($correlationId))
            ->map(fn (array $s) => [
                'recorded_at' => date(DATE_ATOM, (int) $s['at']),
                'method' => $s['method'],
                'path' => $s['path'],
                'status' => $s['status'],
                'duration_ms' => $s['duration_ms'],
            ])
            ->values();

        if ($requests->isEmpty()) {
            return response()->json([
                'found' => false,
                'correlation_id' => $correlationId,
                'message' => 'Tidak ada request tercatat dengan Correlation ID ini (mungkin sudah terhapus dari catatan terbaru, atau tidak pernah ada).',
            ], 404);
        }

        $slowQueries = collect($observability->getSlowRequests())
            ->filter(fn (array $entry) => ($entry['correlation_id'] ?? null) === $correlationId)
            ->flatMap(fn (array $entry) => $entry['slow_queries'] ?? [])
            ->map(fn (array $q) => ['sql' => $q['sql'] ?? '', 'time_ms' => $q['time_ms'] ?? 0])
            ->values();

        return response()->json([
            'found' => true,
            'correlation_id' => $correlationId,
            'total_latency_ms' => round((float) $requests->sum('duration_ms'), 2),
            'requests' => $requests,
            'slow_queries' => $slowQueries,
        ]);
    }
}
