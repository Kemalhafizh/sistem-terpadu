<?php

namespace App\Services\Performance;

use App\Models\LoadTestResult;

class LoadTestService
{
    /**
     * Run simulated load test stress scenario.
     */
    public function runScenario(string $scenarioName, int $concurrentUsers = 100, int $durationSeconds = 30): LoadTestResult
    {
        $totalRequests = $concurrentUsers * $durationSeconds * 5;
        $rps = round($totalRequests / $durationSeconds, 1);

        return LoadTestResult::create([
            'scenario_name' => $scenarioName,
            'concurrent_users' => $concurrentUsers,
            'duration_seconds' => $durationSeconds,
            'total_requests' => $totalRequests,
            'requests_per_second' => $rps,
            'p50_latency_ms' => 15.2,
            'p95_latency_ms' => 45.8,
            'p99_latency_ms' => 88.4,
            'error_rate_percent' => 0.0,
            'status' => 'completed',
            'started_at' => now(),
            'completed_at' => now()->addSeconds($durationSeconds),
        ]);
    }

    /**
     * Compare load test results against baseline.
     */
    public function compareResults(int $testId): array
    {
        $current = LoadTestResult::findOrFail($testId);
        $baseline = LoadTestResult::where('id', '<', $testId)->orderBy('id', 'desc')->first();

        $baselineRps = $baseline ? $baseline->requests_per_second : $current->requests_per_second;
        $diffRps = round($current->requests_per_second - $baselineRps, 1);

        return [
            'current' => $current,
            'baseline' => $baseline,
            'rps_difference' => $diffRps,
            'performance_trend' => $diffRps >= 0 ? 'improving' : 'declining',
        ];
    }
}
