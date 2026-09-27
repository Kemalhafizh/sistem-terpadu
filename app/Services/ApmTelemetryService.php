<?php

namespace App\Services;

use App\Services\Observability\RequestMetricsRecorder;

/**
 * Figures for the admin APM dashboard, all measured.
 *
 * This used to return fixed numbers — P50 35 / P90 65 / P99 98 ms, uptime
 * 99.98%, "HEALTHY (EXCEEDS SLA TARGET)" — and two invented slow queries,
 * whatever the server was doing. Latency and availability now come from
 * RequestMetricsRecorder (every web/API request), slow queries from the
 * PerformanceLogger log. Anything not measured is null.
 */
class ApmTelemetryService
{
    public function __construct(
        private RequestMetricsRecorder $recorder,
        private ObservabilityService $observability,
    ) {}

    public function getTelemetryMetrics(): array
    {
        $summary = $this->recorder->summary();
        $targetP99 = (float) config('apm.target_p99_ms', 100);
        $targetSla = (float) config('apm.sla_target_percent', 99.9);

        // Memory of the PHP process serving this page (per-request, not the server's RAM).
        $memoryUsedMb = round(memory_get_usage(true) / 1024 / 1024, 2);
        $memoryPeakMb = round(memory_get_peak_usage(true) / 1024 / 1024, 2);

        $diskFreeBytes = @disk_free_space(base_path());
        $diskTotalBytes = @disk_total_space(base_path());
        $hasDisk = $diskFreeBytes !== false && $diskTotalBytes !== false && $diskTotalBytes > 0;

        return [
            'latency' => [
                'p50' => $summary['p50'],
                'p90' => $summary['p90'],
                'p99' => $summary['p99'],
                'target_p99' => $targetP99,
                'is_p99_healthy' => $summary['p99'] === null ? null : $summary['p99'] <= $targetP99,
                'sample_count' => $summary['sample_count'],
                'window_start' => $summary['window_start'],
                'window_end' => $summary['window_end'],
                'window_hours' => (int) config('apm.window_hours', 24),
            ],
            'memory' => [
                'used_mb' => $memoryUsedMb,
                'peak_mb' => $memoryPeakMb,
                'limit_mb' => ini_get('memory_limit'),
            ],
            'storage' => [
                'free_gb' => $hasDisk ? round($diskFreeBytes / 1024 / 1024 / 1024, 2) : null,
                'total_gb' => $hasDisk ? round($diskTotalBytes / 1024 / 1024 / 1024, 2) : null,
                'used_percent' => $hasDisk ? round(($diskTotalBytes - $diskFreeBytes) / $diskTotalBytes * 100, 1) : null,
            ],
            'sla' => $this->sla($summary, $targetSla),
        ];
    }

    /**
     * Request-based availability (share of requests not answered 5xx) against
     * the target, and how much of the error budget is left. Server uptime is
     * not measured here — that needs a monitor outside the app, since a down
     * server records nothing — so it is reported as null.
     */
    private function sla(array $summary, float $target): array
    {
        $availability = $summary['availability'];
        $budget = 100 - $target;

        $remaining = null;
        if ($availability !== null && $budget > 0) {
            $consumed = 100 - $availability;
            $remaining = round(max(0, ($budget - $consumed) / $budget * 100), 1);
        }

        $status = match (true) {
            $availability === null => 'NO DATA',
            $availability >= $target => 'MEETS TARGET',
            default => 'BELOW TARGET',
        };

        return [
            'target' => $target,
            'availability' => $availability,
            'uptime' => null,
            'error_count' => $summary['error_count'],
            'request_count' => $summary['sample_count'],
            // Percent of the error budget still unused (100 = no 5xx at all).
            'error_budget_remaining' => $remaining,
            'status' => $status,
        ];
    }

    /**
     * Slowest queries actually captured by PerformanceLogger
     * (≥ config('apm.slow_query_ms')), newest log first, slowest first.
     */
    public function getSlowQueries(int $limit = 20): array
    {
        $threshold = (float) config('apm.slow_query_ms', 500);

        return collect($this->observability->getSlowQueries())
            ->take($limit)
            ->values()
            ->map(fn (array $q, int $i) => [
                'id' => 'SQ-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'query' => $q['sql'],
                'duration_ms' => round((float) $q['time_ms'], 2),
                'caller' => $q['url'] ?: '-',
                'logged_at' => $q['timestamp'] ?: null,
                'threshold_ms' => $threshold,
            ])
            ->all();
    }
}
