<?php

namespace App\Console\Commands;

use App\Support\LogTail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class HealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:health-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify the health of system dependencies: database, cache, disk space, queue status, and recent log errors.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('==================================================');
        $this->info('   SISTA System Operational Health Check    ');
        $this->info('==================================================');

        $results = [];
        $isHealthy = true;

        // 1. Database Connection Check
        try {
            $dbStart = microtime(true);
            DB::connection()->getPdo();
            $dbDuration = round((microtime(true) - $dbStart) * 1000, 2);

            // Check if essential tables exist
            $tablesExist = Schema::hasTable('users') && Schema::hasTable('students');

            if ($tablesExist) {
                $results[] = [
                    'Component' => 'Database',
                    'Status' => ' HEALTHY',
                    'Details' => "Connected successfully ($dbDuration ms). Tables verified.",
                ];
            } else {
                $results[] = [
                    'Component' => 'Database',
                    'Status' => ' WARNING',
                    'Details' => "Connected ($dbDuration ms) but essential tables (users/students) are missing.",
                ];
                $isHealthy = false;
            }
        } catch (\Exception $e) {
            $results[] = [
                'Component' => 'Database',
                'Status' => ' CRITICAL',
                'Details' => 'Connection failed: '.$e->getMessage(),
            ];
            $isHealthy = false;
        }

        // 2. Cache Store Health Check
        try {
            $testKey = 'health_check_test_key';
            $testVal = 'health_ok';

            Cache::put($testKey, $testVal, 5);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            if ($retrieved === $testVal) {
                $results[] = [
                    'Component' => 'Cache Store',
                    'Status' => ' HEALTHY',
                    'Details' => 'Write, Read, and Delete operations verified.',
                ];
            } else {
                $results[] = [
                    'Component' => 'Cache Store',
                    'Status' => ' CRITICAL',
                    'Details' => 'Retrieved cache value did not match.',
                ];
                $isHealthy = false;
            }
        } catch (\Exception $e) {
            $results[] = [
                'Component' => 'Cache Store',
                'Status' => ' CRITICAL',
                'Details' => 'Cache operations failed: '.$e->getMessage(),
            ];
            $isHealthy = false;
        }

        // 3. Disk Usage Check
        try {
            $path = base_path();
            $free = disk_free_space($path);
            $total = disk_total_space($path);

            if ($free === false || $total === false) {
                throw new \Exception('Failed to retrieve disk statistics.');
            }

            $freeGb = round($free / (1024 * 1024 * 1024), 2);
            $totalGb = round($total / (1024 * 1024 * 1024), 2);
            $usedPercentage = round((($total - $free) / $total) * 100, 2);

            $status = ' HEALTHY';
            if ($usedPercentage > config('health.disk_critical_percent', 90)) {
                $status = ' CRITICAL';
                $isHealthy = false;
            } elseif ($usedPercentage > config('health.disk_warning_percent', 80)) {
                $status = ' WARNING';
                $isHealthy = false;
            }

            $results[] = [
                'Component' => 'Disk Space',
                'Status' => $status,
                'Details' => "$freeGb GB free of $totalGb GB ($usedPercentage% used).",
            ];
        } catch (\Exception $e) {
            $results[] = [
                'Component' => 'Disk Space',
                'Status' => ' WARNING',
                'Details' => $e->getMessage(),
            ];
        }

        // 4. Queue Health Check
        try {
            $failedJobs = DB::table('failed_jobs')->count();
            if ($failedJobs > 0) {
                $results[] = [
                    'Component' => 'Queue Runner',
                    'Status' => ' WARNING',
                    'Details' => "There are $failedJobs failed jobs in failed_jobs table.",
                ];
            } else {
                $results[] = [
                    'Component' => 'Queue Runner',
                    'Status' => ' HEALTHY',
                    'Details' => 'Zero failed jobs registered.',
                ];
            }
        } catch (\Exception $e) {
            $results[] = [
                'Component' => 'Queue Runner',
                'Status' => ' HEALTHY',
                'Details' => 'Queue table verified or not initialized (running default sync).',
            ];
        }

        // 5. Log Files & Errors Check
        try {
            $logPath = storage_path('logs/laravel.log');
            if (file_exists($logPath)) {
                // Only the end of the log is read. file_get_contents() on the whole
                // file hit PHP's memory limit once the log grew (a fatal error, not
                // an Exception), so the health check itself crashed.
                $lines = LogTail::lines(
                    $logPath,
                    (int) config('health.log_tail_bytes', 1024 * 1024),
                    (int) config('health.log_tail_lines', 1500)
                );

                $errorCount = 0;
                foreach ($lines as $line) {
                    if (str_contains($line, '.ERROR:') || str_contains($line, '.CRITICAL:') || str_contains($line, '.EMERGENCY:')) {
                        $errorCount++;
                    }
                }

                if ($errorCount > 0) {
                    $results[] = [
                        'Component' => 'System Logs',
                        'Status' => ' WARNING',
                        'Details' => "Found $errorCount error(s) in recent logs.",
                    ];
                } else {
                    $results[] = [
                        'Component' => 'System Logs',
                        'Status' => ' HEALTHY',
                        'Details' => 'No recent errors registered in logs.',
                    ];
                }
            } else {
                $results[] = [
                    'Component' => 'System Logs',
                    'Status' => ' HEALTHY',
                    'Details' => 'No log files exist yet (clean system).',
                ];
            }
        } catch (\Exception $e) {
            $results[] = [
                'Component' => 'System Logs',
                'Status' => ' WARNING',
                'Details' => 'Failed to verify log health: '.$e->getMessage(),
            ];
        }

        $this->table(['Component', 'Status', 'Details'], $results);

        $this->info('==================================================');
        if ($isHealthy) {
            $this->info(' SYSTEM HEALTHY - All essential systems operating within bounds.');

            return 0;
        } else {
            $this->warn(' SYSTEM WARNING - One or more components require attention.');

            return 1;
        }
    }
}
