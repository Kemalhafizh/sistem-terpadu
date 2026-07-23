<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdaptiveRiskEngine
{
    /**
     * Calculate security risk score (0 - 100) for a login or session request.
     */
    public function calculateRiskScore(User $user, Request $request): array
    {
        $score = 0;
        $factors = [];

        $ip = $request->ip();
        $userAgent = $request->userAgent() ?? 'Unknown';

        // 1. IP Subnet Evaluation
        $subnet = $this->extractSubnet($ip);
        $knownSubnets = Cache::get("user_known_subnets_{$user->id}", []);

        if (empty($knownSubnets) || ! in_array($subnet, $knownSubnets)) {
            $score += 30;
            $factors[] = 'UNFAMILIAR_IP_SUBNET';
        }

        // 2. Device Fingerprint Evaluation
        $deviceHash = hash('sha256', $userAgent);
        $knownDevices = Cache::get("user_known_devices_{$user->id}", []);

        if (empty($knownDevices) || ! in_array($deviceHash, $knownDevices)) {
            $score += 30;
            $factors[] = 'UNFAMILIAR_DEVICE_FINGERPRINT';
        }

        // 3. Time Anomaly Evaluation (Off-hours access 00:00 - 04:00)
        $currentHour = (int) now()->format('H');
        if ($currentHour >= 0 && $currentHour < 4) {
            $score += 20;
            $factors[] = 'OFF_HOURS_ACCESS';
        }

        // 4. Suspicious / Missing User Agent
        if (empty($userAgent) || str_contains(strtolower($userAgent), 'curl') || str_contains(strtolower($userAgent), 'python')) {
            $score += 40;
            $factors[] = 'AUTOMATED_SCRIPT_HEADER';
        }

        $riskLevel = match (true) {
            $score >= 70 => 'HIGH_RISK',
            $score >= 30 => 'MEDIUM_RISK',
            default => 'LOW_RISK',
        };

        return [
            'score' => min(100, $score),
            'risk_level' => $riskLevel,
            'factors' => $factors,
            'requires_mfa' => $score >= 30,
        ];
    }

    /**
     * Check if request should trigger Step-Up MFA Challenge.
     */
    public function shouldChallengeMfa(User $user, Request $request): bool
    {
        $assessment = $this->calculateRiskScore($user, $request);
        return $assessment['requires_mfa'];
    }

    /**
     * Register device & IP subnet as trusted for the user.
     */
    public function registerKnownDevice(User $user, Request $request): void
    {
        $subnet = $this->extractSubnet($request->ip());
        $knownSubnets = Cache::get("user_known_subnets_{$user->id}", []);
        if (! in_array($subnet, $knownSubnets)) {
            $knownSubnets[] = $subnet;
            Cache::put("user_known_subnets_{$user->id}", $knownSubnets, now()->addDays(90));
        }

        $deviceHash = hash('sha256', $request->userAgent() ?? 'Unknown');
        $knownDevices = Cache::get("user_known_devices_{$user->id}", []);
        if (! in_array($deviceHash, $knownDevices)) {
            $knownDevices[] = $deviceHash;
            Cache::put("user_known_devices_{$user->id}", $knownDevices, now()->addDays(90));
        }
    }

    /**
     * Extract Subnet (/24 or IPv6 prefix) from IP.
     */
    private function extractSubnet(?string $ip): string
    {
        if (! $ip) {
            return '127.0.0';
        }

        $parts = explode('.', $ip);
        if (count($parts) === 4) {
            return "{$parts[0]}.{$parts[1]}.{$parts[2]}";
        }

        return substr($ip, 0, 10);
    }
}
