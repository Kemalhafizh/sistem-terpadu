<?php

declare(strict_types=1);

namespace App\Services\Mobile;

use Firebase\JWT\JWT;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Firebase Cloud Messaging HTTP v1 sender.
 *
 * Authenticates with a Google service account (config services.fcm.credentials:
 * a path to the JSON key file, or the JSON itself) by signing an RS256 JWT and
 * exchanging it for an OAuth access token, which is cached until shortly
 * before it expires. Without credentials, isConfigured() is false and callers
 * fall back to logging only.
 */
class FcmHttpV1Client
{
    public const RESULT_SENT = 'sent';

    /** The token is dead (app uninstalled, token rotated): stop using it. */
    public const RESULT_INVALID_TOKEN = 'invalid_token';

    /** Quota or server error: worth retrying later. */
    public const RESULT_RETRY = 'retry';

    /** Rejected for another reason (bad payload, auth): retrying won't help. */
    public const RESULT_FAILED = 'failed';

    private const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    private const TOKEN_CACHE_KEY = 'fcm:http_v1:access_token';

    /** @var array<string, mixed>|null|false false = not loaded yet */
    private array|null|false $credentials = false;

    public function isConfigured(): bool
    {
        $credentials = $this->credentials();

        return $credentials !== null && $this->projectId() !== null;
    }

    public function projectId(): ?string
    {
        $configured = config('services.fcm.project_id');

        return filled($configured) ? (string) $configured : ($this->credentials()['project_id'] ?? null);
    }

    /**
     * Send one message to one device token.
     *
     * @param  array<string, string>  $data  string values only (FCM v1 requirement)
     * @return self::RESULT_*
     */
    public function send(string $deviceToken, string $title, string $body, array $data, string $androidChannelId): string
    {
        $message = [
            'message' => [
                'token' => $deviceToken,
                'notification' => ['title' => $title, 'body' => $body],
                'data' => $data,
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => [
                        'channel_id' => $androidChannelId,
                        'sound' => 'default',
                    ],
                ],
            ],
        ];

        try {
            $response = $this->post($message);
            if ($response->status() === 401) {
                // Access token revoked or expired early: fetch a fresh one once.
                Cache::forget(self::TOKEN_CACHE_KEY);
                $response = $this->post($message);
            }
        } catch (Throwable $e) {
            Log::warning('FCM: request failed', ['error' => $e->getMessage()]);

            return self::RESULT_RETRY;
        }

        if ($response->successful()) {
            return self::RESULT_SENT;
        }

        $status = $response->status();
        $errorCode = $this->fcmErrorCode($response->json());

        if ($status === 404 || $errorCode === 'UNREGISTERED'
            || ($status === 400 && $errorCode === 'INVALID_ARGUMENT' && str_contains((string) $response->json('error.message'), 'registration token'))) {
            return self::RESULT_INVALID_TOKEN;
        }
        if ($status === 429 || $status >= 500) {
            return self::RESULT_RETRY;
        }

        Log::warning('FCM: message rejected', ['status' => $status, 'error' => $response->json('error.message')]);

        return self::RESULT_FAILED;
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function post(array $message): Response
    {
        return Http::withToken($this->accessToken())
            ->timeout((int) config('services.fcm.timeout', 10))
            ->post("https://fcm.googleapis.com/v1/projects/{$this->projectId()}/messages:send", $message);
    }

    private function accessToken(): string
    {
        $cached = Cache::get(self::TOKEN_CACHE_KEY);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $credentials = $this->credentials() ?? throw new RuntimeException('FCM credentials are not configured.');
        $tokenUri = $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token';
        $now = time();

        $assertion = JWT::encode([
            'iss' => $credentials['client_email'],
            'scope' => self::SCOPE,
            'aud' => $tokenUri,
            'iat' => $now,
            'exp' => $now + 3600,
        ], $credentials['private_key'], 'RS256');

        $response = Http::asForm()
            ->timeout((int) config('services.fcm.timeout', 10))
            ->post($tokenUri, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ]);

        $token = $response->json('access_token');
        if (! $response->successful() || ! is_string($token)) {
            throw new RuntimeException('FCM OAuth token request failed: HTTP '.$response->status());
        }

        // Google tokens live 3600 s; refresh five minutes early.
        $ttl = max(60, (int) $response->json('expires_in', 3600) - 300);
        Cache::put(self::TOKEN_CACHE_KEY, $token, $ttl);

        return $token;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function credentials(): ?array
    {
        if ($this->credentials !== false) {
            return $this->credentials;
        }

        $raw = config('services.fcm.credentials');
        $json = null;
        if (is_array($raw)) {
            $json = $raw;
        } elseif (is_string($raw) && $raw !== '') {
            $text = str_starts_with(ltrim($raw), '{') ? $raw : (is_readable($raw) ? (string) file_get_contents($raw) : null);
            $json = $text !== null ? json_decode($text, true) : null;
        }

        return $this->credentials = is_array($json) && isset($json['client_email'], $json['private_key']) ? $json : null;
    }

    /**
     * FCM puts its own error code in error.details[].errorCode.
     *
     * @param  array<string, mixed>|null  $body
     */
    private function fcmErrorCode(?array $body): ?string
    {
        foreach ($body['error']['details'] ?? [] as $detail) {
            if (isset($detail['errorCode'])) {
                return (string) $detail['errorCode'];
            }
        }

        return $body['error']['status'] ?? null;
    }
}
