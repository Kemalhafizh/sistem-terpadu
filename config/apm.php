<?php

/*
 * Slow-request logging (App\Http\Middleware\PerformanceLogger). Requests and
 * queries at or above these durations are appended to
 * storage/logs/observability.json.
 */
return [

    'slow_query_ms' => (float) env('APM_SLOW_QUERY_MS', 500),
    'slow_request_ms' => (float) env('APM_SLOW_REQUEST_MS', 1000),

];
