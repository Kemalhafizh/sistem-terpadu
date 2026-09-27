<?php

namespace App\Services\Observability;

use App\Support\LogTail;

/**
 * Append-only record of every web/API request (written by PerformanceLogger)
 * and the real latency / availability figures derived from it.
 *
 * One tab-separated line per request:
 *   unix-time  duration-ms  status  method  path  correlation-id
 * The path never includes the query string, so tokens in URLs are not stored.
 */
class RequestMetricsRecorder
{
    /** Bytes read from the end of the file when summarising (≈ 80 B per line). */
    private const READ_BYTES_PER_LINE = 160;

    public function path(): string
    {
        return storage_path('logs/'.config('apm.metrics_file', 'request-metrics.log'));
    }

    public function record(string $method, string $path, int $status, float $durationMs, ?string $correlationId, ?float $at = null): void
    {
        if (! config('apm.record_requests')) {
            return;
        }

        $line = implode("\t", [
            // Truncate, don't round: %.3F rounds up half the time, stamping the
            // sample up to 0.5 ms in the future so an immediate summary() drops it.
            sprintf('%.3F', $at ?? floor(microtime(true) * 1000) / 1000),
            sprintf('%.2F', max(0, $durationMs)),
            $status,
            self::clean(strtoupper($method), 10),
            self::clean('/'.ltrim($path, '/'), 255),
            self::clean((string) $correlationId, 64),
        ])."\n";

        $file = $this->path();
        $dir = dirname($file);
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
        $this->trimIfNeeded($file);
    }

    /**
     * Latency percentiles (nearest-rank) and request availability over the
     * configured window. Every figure is null when nothing was recorded.
     */
    public function summary(?float $now = null): array
    {
        $now ??= microtime(true);
        $since = $now - (int) config('apm.window_hours', 24) * 3600;

        $samples = array_values(array_filter(
            $this->samples(),
            fn (array $s) => $s['at'] >= $since && $s['at'] <= $now
        ));
        $count = count($samples);

        if ($count === 0) {
            return [
                'sample_count' => 0,
                'window_start' => null,
                'window_end' => null,
                'p50' => null,
                'p90' => null,
                'p99' => null,
                'error_count' => 0,
                'availability' => null,
            ];
        }

        $durations = array_column($samples, 'duration_ms');
        sort($durations);
        $errors = count(array_filter($samples, fn (array $s) => $s['status'] >= 500));
        $times = array_column($samples, 'at');

        return [
            'sample_count' => $count,
            'window_start' => date(DATE_ATOM, (int) min($times)),
            'window_end' => date(DATE_ATOM, (int) max($times)),
            'p50' => self::percentile($durations, 50),
            'p90' => self::percentile($durations, 90),
            'p99' => self::percentile($durations, 99),
            'error_count' => $errors,
            'availability' => round(($count - $errors) / $count * 100, 3),
        ];
    }

    /** Requests recorded with this correlation id, oldest first. */
    public function findByCorrelationId(string $correlationId): array
    {
        $needle = self::clean($correlationId, 64);
        if ($needle === '') {
            return [];
        }

        return array_values(array_filter(
            $this->samples(),
            fn (array $s) => $s['correlation_id'] === $needle
        ));
    }

    /** @return array<int, array{at: float, duration_ms: float, status: int, method: string, path: string, correlation_id: string}> */
    public function samples(): array
    {
        $keep = max(1, (int) config('apm.keep_lines', 30000));
        $lines = LogTail::lines($this->path(), $keep * self::READ_BYTES_PER_LINE, $keep);

        $samples = [];
        foreach ($lines as $line) {
            $parts = explode("\t", $line);
            if (count($parts) !== 6 || ! is_numeric($parts[0]) || ! is_numeric($parts[1]) || ! ctype_digit($parts[2])) {
                continue; // partial or foreign line
            }
            $samples[] = [
                'at' => (float) $parts[0],
                'duration_ms' => (float) $parts[1],
                'status' => (int) $parts[2],
                'method' => $parts[3],
                'path' => $parts[4],
                'correlation_id' => $parts[5],
            ];
        }

        return $samples;
    }

    /** Nearest-rank percentile of an ascending list. */
    public static function percentile(array $sortedAscending, float $p): ?float
    {
        $n = count($sortedAscending);
        if ($n === 0) {
            return null;
        }
        $rank = (int) max(1, ceil($p / 100 * $n));

        return round((float) $sortedAscending[min($n, $rank) - 1], 2);
    }

    /** Keeps the file bounded: once past max_bytes, only the newest keep_lines remain. */
    private function trimIfNeeded(string $file): void
    {
        clearstatcache(true, $file);
        $size = @filesize($file);
        if ($size === false || $size <= (int) config('apm.max_bytes', 4 * 1024 * 1024)) {
            return;
        }

        $keep = max(1, (int) config('apm.keep_lines', 30000));
        $lines = LogTail::lines($file, $keep * self::READ_BYTES_PER_LINE, $keep);
        @file_put_contents($file, $lines ? implode("\n", $lines)."\n" : '', LOCK_EX);
    }

    /** Strips tabs/newlines/control characters so one request is always one well-formed line. */
    private static function clean(string $value, int $maxLength): string
    {
        return mb_substr((string) preg_replace('/[\x00-\x1F\x7F]+/', '', $value), 0, $maxLength);
    }
}
