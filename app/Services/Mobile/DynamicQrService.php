<?php

declare(strict_types=1);

namespace App\Services\Mobile;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DynamicQrService
{
    protected string $secretKey;

    public function __construct()
    {
        $this->secretKey = config('app.key') ?? 'sulaone-secret-key-32bytes-hex-ok';
    }

    /**
     * Generate dynamic rotating TOTP QR payload (valid for 30s).
     */
    public function generateDynamicToken(User $user): array
    {
        $timeWindow = (int) floor(time() / 30);
        $payload = json_encode([
            'uid' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'w' => $timeWindow,
            'salt' => bin2hex(random_bytes(4)),
        ]);

        $signature = hash_hmac('sha256', $payload, $this->secretKey);
        $encodedToken = base64_encode($payload . '::' . $signature);

        $expiresIn = 30 - (time() % 30);

        return [
            'token' => $encodedToken,
            'valid_seconds' => 30,
            'expires_in' => $expiresIn,
            'user_id' => $user->id,
            'name' => $user->name,
        ];
    }

    /**
     * Verify a dynamic rotating TOTP QR token.
     */
    public function verifyDynamicToken(string $encodedToken): array
    {
        try {
            $decoded = base64_decode($encodedToken);
            $parts = explode('::', $decoded);
            if (count($parts) !== 2) {
                return ['valid' => false, 'message' => 'Invalid token structure'];
            }

            [$payloadJson, $providedSignature] = $parts;
            $expectedSignature = hash_hmac('sha256', $payloadJson, $this->secretKey);

            if (!hash_equals($expectedSignature, $providedSignature)) {
                return ['valid' => false, 'message' => 'Cryptographic signature mismatch'];
            }

            $payload = json_decode($payloadJson, true);
            $currentWindow = (int) floor(time() / 30);
            $tokenWindow = $payload['w'] ?? 0;

            // Allow current window and previous window (tolerates up to 30s clock drift)
            if (abs($currentWindow - $tokenWindow) > 1) {
                return ['valid' => false, 'message' => 'QR Code has expired (over 30s)'];
            }

            // Check replay cache
            $cacheKey = 'dynamic_qr_used:' . substr($providedSignature, 0, 16);
            if (Cache::has($cacheKey)) {
                return ['valid' => false, 'message' => 'QR Code has already been used'];
            }
            Cache::put($cacheKey, true, 60);

            $user = User::find($payload['uid']);
            if (!$user) {
                return ['valid' => false, 'message' => 'User not found'];
            }

            return [
                'valid' => true,
                'user_id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'verified_at' => now()->toIso8601String(),
            ];
        } catch (\Throwable $e) {
            return ['valid' => false, 'message' => 'Verification error: ' . $e->getMessage()];
        }
    }
}
