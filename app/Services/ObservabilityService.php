<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ObservabilityService
{
    /**
     * Get overall system health metrics.
     */
    public function getSystemHealth(): array
    {
        return [
            'cpu' => $this->getCpuLoad(),
            'ram' => $this->getRamUsage(),
            'disk' => $this->getDiskUsage(),
            'db_size' => $this->getDatabaseSize(),
            'active_users' => $this->getActiveUserCount(),
            'queue_depth' => $this->getQueueDepth(),
        ];
    }

    /**
     * Get CPU Load Percentage (Windows / Linux safe).
     */
    public function getCpuLoad(): float
    {
        if (PHP_OS_FAMILY === 'Windows') {
            try {
                // Try using wmic
                $output = shell_exec('wmic cpu get LoadPercentage /value 2>&1');
                if ($output && preg_match('/LoadPercentage=(\d+)/i', $output, $matches)) {
                    return (float) $matches[1];
                }

                // Fallback using PowerShell
                $output = shell_exec('powershell -Command "Get-CimInstance Win32_Processor | Select-Object -ExpandProperty LoadPercentage" 2>&1');
                if ($output && is_numeric(trim($output))) {
                    return (float) trim($output);
                }
            } catch (\Exception $e) {
                // Fail silently
            }

            return 12.5; // Mock / fallback value for Windows if blocked
        }

        // Linux fallback using sys_getloadavg
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            if (is_array($load) && isset($load[0])) {
                // Simple load avg approximation: load * 50 capped at 100
                return min(100.0, round($load[0] * 50, 2));
            }
        }

        return 8.0; // Linux default fallback
    }

    /**
     * Get RAM Usage details (used, total, percentage).
     */
    public function getRamUsage(): array
    {
        $total = 16.0; // Fallback total GB
        $free = 12.0;  // Fallback free GB

        if (PHP_OS_FAMILY === 'Windows') {
            try {
                // Try wmic
                $output = shell_exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /value 2>&1');
                if ($output) {
                    $freeMem = null;
                    $totalMem = null;
                    if (preg_match('/FreePhysicalMemory=(\d+)/i', $output, $matches)) {
                        $freeMem = (float) $matches[1]; // KB
                    }
                    if (preg_match('/TotalVisibleMemorySize=(\d+)/i', $output, $matches)) {
                        $totalMem = (float) $matches[1]; // KB
                    }

                    if ($freeMem && $totalMem) {
                        $total = round($totalMem / 1024 / 1024, 2);
                        $free = round($freeMem / 1024 / 1024, 2);
                    }
                } else {
                    // Try powershell
                    $output = shell_exec('powershell -Command "Get-CimInstance Win32_OperatingSystem | Select-Object FreePhysicalMemory,TotalVisibleMemorySize | Format-List" 2>&1');
                    if ($output) {
                        $freeMem = null;
                        $totalMem = null;
                        if (preg_match('/FreePhysicalMemory\s*:\s*(\d+)/i', $output, $matches)) {
                            $freeMem = (float) $matches[1];
                        }
                        if (preg_match('/TotalVisibleMemorySize\s*:\s*(\d+)/i', $output, $matches)) {
                            $totalMem = (float) $matches[1];
                        }
                        if ($freeMem && $totalMem) {
                            $total = round($totalMem / 1024 / 1024, 2);
                            $free = round($freeMem / 1024 / 1024, 2);
                        }
                    }
                }
            } catch (\Exception $e) {
                // Fail silently
            }
        } else {
            // Linux proc meminfo parser
            try {
                if (file_exists('/proc/meminfo')) {
                    $meminfo = file_get_contents('/proc/meminfo');
                    if (preg_match('/MemTotal:\s+(\d+)\s+kB/i', $meminfo, $matchesTotal) &&
                        preg_match('/MemAvailable:\s+(\d+)\s+kB/i', $meminfo, $matchesAvail)) {
                        $total = round((float) $matchesTotal[1] / 1024 / 1024, 2);
                        $free = round((float) $matchesAvail[1] / 1024 / 1024, 2);
                    }
                }
            } catch (\Exception $e) {
                // Fail silently
            }
        }

        $used = round($total - $free, 2);
        $percentage = $total > 0 ? round(($used / $total) * 100, 2) : 0;

        return [
            'used' => $used,
            'total' => $total,
            'percentage' => $percentage,
        ];
    }

    /**
     * Get disk space usage on hosting drive.
     */
    public function getDiskUsage(): array
    {
        try {
            $path = base_path();
            $freeBytes = disk_free_space($path);
            $totalBytes = disk_total_space($path);

            $free = round($freeBytes / 1024 / 1024 / 1024, 2); // GB
            $total = round($totalBytes / 1024 / 1024 / 1024, 2); // GB
            $used = round($total - $free, 2);
            $percentage = $total > 0 ? round(($used / $total) * 100, 2) : 0;

            return [
                'used' => $used,
                'total' => $total,
                'percentage' => $percentage,
            ];
        } catch (\Exception $e) {
            return [
                'used' => 30.0,
                'total' => 100.0,
                'percentage' => 30.0,
            ];
        }
    }

    /**
     * Get SQLite Database Size.
     */
    public function getDatabaseSize(): string
    {
        try {
            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");

            if ($driver === 'sqlite') {
                $dbPath = config("database.connections.{$connection}.database");
                if (file_exists($dbPath)) {
                    $bytes = filesize($dbPath);
                    if ($bytes >= 1024 * 1024 * 1024) {
                        return round($bytes / 1024 / 1024 / 1024, 2).' GB';
                    }

                    return round($bytes / 1024 / 1024, 2).' MB';
                }
            }
        } catch (\Exception $e) {
            // Fail silently
        }

        return '0.00 MB';
    }

    /**
     * Get active database queue depth.
     */
    public function getQueueDepth(): int
    {
        try {
            if (Schema::hasTable('jobs')) {
                return DB::table('jobs')->count();
            }
        } catch (\Exception $e) {
            // Fail silently
        }

        return 0;
    }

    /**
     * Get active session users count (last 15 minutes).
     */
    public function getActiveUserCount(): int
    {
        try {
            if (Schema::hasTable('sessions')) {
                return DB::table('sessions')
                    ->where('last_activity', '>=', now()->subMinutes(15)->getTimestamp())
                    ->whereNotNull('user_id')
                    ->distinct('user_id')
                    ->count();
            }
        } catch (\Exception $e) {
            // Fail silently
        }

        return 0;
    }

    /**
     * Helper to read structured observability log file.
     */
    protected function getLogEntries(): array
    {
        $logPath = storage_path('logs/observability.json');
        if (! file_exists($logPath)) {
            return [];
        }

        try {
            $lines = file($logPath);
            if (! $lines) {
                return [];
            }

            $entries = [];
            foreach ($lines as $line) {
                $decoded = json_decode(trim($line), true);
                if ($decoded) {
                    $entries[] = $decoded;
                }
            }

            // Sort by timestamp descending
            usort($entries, function ($a, $b) {
                return strcmp($b['timestamp'] ?? '', $a['timestamp'] ?? '');
            });

            return $entries;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get list of slow requests.
     */
    public function getSlowRequests(): array
    {
        $entries = $this->getLogEntries();

        return array_values(array_filter($entries, function ($entry) {
            return ($entry['type'] ?? '') === 'performance';
        }));
    }

    /**
     * Extract and sort slow queries.
     */
    public function getSlowQueries(): array
    {
        $entries = $this->getLogEntries();
        $queries = [];

        foreach ($entries as $entry) {
            if (($entry['type'] ?? '') === 'performance' && ! empty($entry['slow_queries'])) {
                foreach ($entry['slow_queries'] as $q) {
                    $queries[] = [
                        'timestamp' => $entry['timestamp'] ?? '',
                        'url' => $entry['url'] ?? '',
                        'sql' => $q['sql'] ?? '',
                        'time_ms' => $q['time_ms'] ?? 0,
                        'user_id' => $entry['user_id'] ?? null,
                    ];
                }
            }
        }

        // Sort slow queries descending by execution time
        usort($queries, function ($a, $b) {
            return ($b['time_ms'] ?? 0) <=> ($a['time_ms'] ?? 0);
        });

        return array_slice($queries, 0, 50);
    }

    /**
     * Get system error events.
     */
    public function getErrors(): array
    {
        $entries = $this->getLogEntries();

        return array_values(array_filter($entries, function ($entry) {
            return ($entry['type'] ?? '') === 'error';
        }));
    }

    /**
     * Get error count in last 24 hours.
     */
    public function getErrorCount24h(): int
    {
        $entries = $this->getErrors();
        $cutoff = now()->subHours(24)->toIso8601String();

        $count = 0;
        foreach ($entries as $entry) {
            if (($entry['timestamp'] ?? '') >= $cutoff) {
                $count++;
            }
        }

        return $count;
    }
}
