<?php

/*
 * Real request telemetry for the admin APM dashboard
 * (App\Services\Observability\RequestMetricsRecorder, PerformanceLogger).
 *
 * The dashboard used to show fixed numbers (P50 35 / P90 65 / P99 98 ms,
 * uptime 99.98%, two invented slow queries). It now reads what was actually
 * recorded.
 */
return [

    // Append one line per web/API request (time, duration, status, method,
    // path without query string, correlation id). Off in the test suite so
    // ordinary tests don't write to storage/logs.
    'record_requests' => (bool) env('APM_RECORD_REQUESTS', env('APP_ENV') !== 'testing'),

    // storage/logs/<file>
    'metrics_file' => 'request-metrics.log',

    // The file is trimmed to the newest `keep_lines` once it exceeds `max_bytes`.
    'max_bytes' => (int) env('APM_METRICS_MAX_BYTES', 4 * 1024 * 1024),
    'keep_lines' => (int) env('APM_METRICS_KEEP_LINES', 30000),

    // Percentiles and availability cover this window (or the newest
    // `keep_lines` requests, whichever is shorter — the dashboard shows which).
    'window_hours' => (int) env('APM_WINDOW_HOURS', 24),

    'target_p99_ms' => (float) env('APM_TARGET_P99_MS', 100),

    // Request-based availability target: share of requests not answered 5xx.
    'sla_target_percent' => (float) env('APM_SLA_TARGET_PERCENT', 99.9),

    // Queries at or above this duration are logged to observability.json.
    'slow_query_ms' => (float) env('APM_SLOW_QUERY_MS', 500),
    // Requests at or above this duration are logged to observability.json.
    'slow_request_ms' => (float) env('APM_SLOW_REQUEST_MS', 1000),

];
