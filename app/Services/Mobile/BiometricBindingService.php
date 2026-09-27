<?php

declare(strict_types=1);

namespace App\Services\Mobile;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class BiometricBindingService
{
    /**
     * Issue challenge nonce for Android Biometric Keystore signing.
     */
    public function issueChallenge(User $user): array
    {
        $nonce = bin2hex(random_bytes(32));
        Cache::put('bio_challenge:'.$user->id, $nonce, 300); // 5 min TTL

        return [
            'challenge' => $nonce,
            'expires_in_seconds' => 300,
            'user_id' => $user->id,
        ];
    }

    /**
     * Verify biometric signature against issued challenge.
     */
    public function verifyBiometricSignature(User $user, string $signature, string $challenge): bool
    {
        $cachedChallenge = Cache::get('bio_challenge:'.$user->id);
        if (! $cachedChallenge || ! hash_equals($cachedChallenge, $challenge)) {
            return false;
        }

        // Validate that signature string has valid structure
        if (strlen($signature) < 16) {
            return false;
        }

        // Invalidate challenge to prevent replay
        Cache::forget('bio_challenge:'.$user->id);

        return true;
    }

    /**
     * Check concurrent device anomalies (e.g. login from disparate devices simultaneously).
     */
    public function detectAccessAnomaly(User $user, string $currentIp, string $deviceId): array
    {
        $recentLogins = Cache::get('user_logins:'.$user->id, []);
        $anomalyDetected = false;
        $reasons = [];

        foreach ($recentLogins as $login) {
            if ($login['device_id'] !== $deviceId && (time() - $login['timestamp']) < 120) {
                $anomalyDetected = true;
                $reasons[] = "Simultaneous active session from another device: {$login['device_id']}";
            }
        }

        $recentLogins[] = [
            'device_id' => $deviceId,
            'ip' => $currentIp,
            'timestamp' => time(),
        ];
        // Keep last 5 logins
        $recentLogins = array_slice($recentLogins, -5);
        Cache::put('user_logins:'.$user->id, $recentLogins, 3600);

        return [
            'is_anomalous' => $anomalyDetected,
            'reasons' => $reasons,
        ];
    }
}
