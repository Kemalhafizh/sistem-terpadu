<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CircuitBreakerService
{
    public const STATE_CLOSED = 'CLOSED';
    public const STATE_OPEN = 'OPEN';
    public const STATE_HALF_OPEN = 'HALF_OPEN';

    private const FAILURE_THRESHOLD = 5;
    private const COOLOFF_SECONDS = 60;

    /**
     * Execute service call protected by Circuit Breaker pattern.
     */
    public function call(string $serviceName, callable $primaryCallback, callable $fallbackCallback)
    {
        $state = $this->getServiceState($serviceName);

        if ($state === self::STATE_OPEN) {
            // Check if cool-off period has passed to transition to HALF_OPEN
            $openedAt = (int) Cache::get("cb_{$serviceName}_opened_at", 0);
            if (time() - $openedAt >= self::COOLOFF_SECONDS) {
                $state = self::STATE_HALF_OPEN;
                Cache::put("cb_{$serviceName}_state", self::STATE_HALF_OPEN);
            } else {
                // Circuit is OPEN: execute fallback immediately without calling primary
                return $fallbackCallback('Circuit Breaker OPEN: Layanan ' . $serviceName . ' dinonaktifkan sementara.');
            }
        }

        try {
            $result = $primaryCallback();
            $this->recordSuccess($serviceName);
            return $result;
        } catch (\Throwable $e) {
            $this->recordFailure($serviceName);
            return $fallbackCallback($e->getMessage());
        }
    }

    /**
     * Record a failure for specified external service.
     */
    public function recordFailure(string $serviceName): void
    {
        $failures = (int) Cache::get("cb_{$serviceName}_failures", 0) + 1;
        Cache::put("cb_{$serviceName}_failures", $failures, 3600);

        if ($failures >= self::FAILURE_THRESHOLD) {
            Cache::put("cb_{$serviceName}_state", self::STATE_OPEN);
            Cache::put("cb_{$serviceName}_opened_at", time());
        }
    }

    /**
     * Record a success for specified external service.
     */
    public function recordSuccess(string $serviceName): void
    {
        Cache::put("cb_{$serviceName}_failures", 0);
        Cache::put("cb_{$serviceName}_state", self::STATE_CLOSED);
        Cache::forget("cb_{$serviceName}_opened_at");
    }

    /**
     * Get state of a service.
     */
    public function getServiceState(string $serviceName): string
    {
        return (string) Cache::get("cb_{$serviceName}_state", self::STATE_CLOSED);
    }

    /**
     * Reset circuit breaker to CLOSED.
     */
    public function resetCircuit(string $serviceName): void
    {
        $this->recordSuccess($serviceName);
    }

    /**
     * Get all monitored external services status summary.
     */
    public function getAllServicesStatus(): array
    {
        $monitoredServices = [
            'fonnte_whatsapp' => 'Fonnte WhatsApp Gateway',
            'midtrans_payment' => 'Midtrans Payment Gateway',
            'kemendikbud_dapodik' => 'Kemendikbud Dapodik API',
        ];

        $statusList = [];
        foreach ($monitoredServices as $key => $name) {
            $state = $this->getServiceState($key);
            $failures = (int) Cache::get("cb_{$key}_failures", 0);
            $openedAt = (int) Cache::get("cb_{$key}_opened_at", 0);

            $statusList[$key] = [
                'key' => $key,
                'name' => $name,
                'state' => $state,
                'failures' => $failures,
                'opened_at' => $openedAt ? date('Y-m-d H:i:s', $openedAt) : null,
                'badge' => match ($state) {
                    self::STATE_CLOSED => 'bg-emerald-500 text-white',
                    self::STATE_OPEN => 'bg-rose-500 text-white animate-pulse',
                    self::STATE_HALF_OPEN => 'bg-amber-500 text-white',
                    default => 'bg-gray-500 text-white',
                },
            ];
        }

        return $statusList;
    }
}
