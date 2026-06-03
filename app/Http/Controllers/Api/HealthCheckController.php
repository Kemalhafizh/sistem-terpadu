<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthCheckController
{
    /**
     * Application health check endpoint.
     *
     * Returns the status of core services: database, cache, and storage.
     * Used by load balancers, monitoring tools, and Docker healthchecks.
     */
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ];

        $healthy = collect($checks)->every(fn ($check) => $check['status'] === 'ok');

        return response()->json([
            'status' => $healthy ? 'healthy' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.version', '1.0.0'),
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }

    /**
     * Check database connectivity.
     */
    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'ok',
                'latency_ms' => $latency,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
            ];
        }
    }

    /**
     * Check cache store connectivity.
     */
    private function checkCache(): array
    {
        try {
            $key = 'health_check_'.uniqid();
            Cache::put($key, true, 10);
            $result = Cache::get($key);
            Cache::forget($key);

            return [
                'status' => $result === true ? 'ok' : 'error',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'Cache store unavailable',
            ];
        }
    }

    /**
     * Check storage disk write access.
     */
    private function checkStorage(): array
    {
        try {
            $testFile = 'health_check_test.tmp';
            Storage::put($testFile, 'ok');
            $content = Storage::get($testFile);
            Storage::delete($testFile);

            return [
                'status' => $content === 'ok' ? 'ok' : 'error',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'Storage disk not writable',
            ];
        }
    }
}
