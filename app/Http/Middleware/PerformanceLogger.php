<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PerformanceLogger
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $queries = [];

        // Attach DB listener to capture queries executed during this request
        DB::listen(function ($query) use (&$queries) {
            $queries[] = [
                'sql' => $query->sql,
                'time_ms' => $query->time, // already in milliseconds in newer Laravel versions
            ];
        });

        $response = $next($request);

        $endTime = microtime(true);
        $durationMs = ($endTime - $startTime) * 1000;
        $peakMemory = memory_get_peak_usage(true); // real allocated size
        // Set by CorrelationIdMiddleware, which runs inside this middleware.
        $correlationId = $request->headers->get('X-Correlation-ID');

        $slowQueryMs = (float) config('apm.slow_query_ms', 500);
        $slowQueries = array_filter($queries, function ($q) use ($slowQueryMs) {
            return $q['time_ms'] >= $slowQueryMs;
        });

        // Threshold slow request OR peak memory > 128MB OR has slow queries
        $isSlowRequest = $durationMs >= (float) config('apm.slow_request_ms', 1000);
        $isHighMemory = ! app()->environment('testing') && $peakMemory >= (128 * 1024 * 1024); // 128MB

        if ($isSlowRequest || $isHighMemory || ! empty($slowQueries)) {
            $this->logPerformance($request, $durationMs, $peakMemory, array_values($slowQueries), $correlationId, $response->getStatusCode());
        }

        return $response;
    }

    /**
     * Log the slow performance request to the JSON log.
     */
    protected function logPerformance(Request $request, float $durationMs, int $peakMemory, array $slowQueries, ?string $correlationId = null, ?int $status = null): void
    {
        try {
            $logPath = storage_path('logs/observability.json');

            // Ensure the directory exists
            $logDir = dirname($logPath);
            if (! file_exists($logDir)) {
                mkdir($logDir, 0755, true);
            }

            $logEntry = [
                'timestamp' => now()->toIso8601String(),
                'type' => 'performance',
                // Path only: the full URL could carry tokens in its query string.
                'url' => '/'.ltrim($request->path(), '/'),
                'method' => $request->method(),
                'status' => $status,
                'correlation_id' => $correlationId,
                'user_id' => auth()->id(),
                'duration_ms' => round($durationMs, 2),
                'memory_mb' => round($peakMemory / 1024 / 1024, 2),
                'slow_queries' => $slowQueries,
            ];

            // Append structured JSON line
            file_put_contents($logPath, json_encode($logEntry)."\n", FILE_APPEND | LOCK_EX);

            // Trim log file to prevent infinite growth (keep last 1000 lines)
            if (file_exists($logPath) && filesize($logPath) > 5 * 1024 * 1024) { // > 5MB
                $lines = file($logPath);
                if (count($lines) > 1000) {
                    $trimmed = array_slice($lines, -1000);
                    file_put_contents($logPath, implode('', $trimmed), LOCK_EX);
                }
            }
        } catch (\Exception $e) {
            // Fail silently to avoid breaking the user request due to logging issues
        }
    }
}
