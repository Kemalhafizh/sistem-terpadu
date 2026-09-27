<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\WhatsappLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    /**
     * Send a WhatsApp message
     *
     * @param  string  $recipient  Phone number of the recipient
     * @param  string  $message  Message body text
     * @param  string|null  $type  Message category/type (e.g. billing_reminder)
     */
    public function sendMessage(string $recipient, string $message, ?string $type = null): WhatsappLog
    {
        // 1. Normalize the phone number format
        $normalized = $this->normalizePhoneNumber($recipient);

        // 2. Check if sandbox/mock mode is active: the admin's setting wins,
        // config (WHATSAPP_GATEWAY_SANDBOX) is only the fallback. This used to
        // be `setting === '1' || env(..., true)`, which is always true — so
        // turning sandbox off in the admin panel never sent a real message.
        $sandboxSetting = Setting::get('whatsapp_sandbox_mode');
        $isSandbox = $sandboxSetting !== null
            ? (string) $sandboxSetting === '1'
            : (bool) config('services.whatsapp.sandbox', true);

        if ($isSandbox) {
            // Simulate delivery failure if phone number contains "999" (great for error state testing)
            if (str_contains($normalized, '999') || str_contains($recipient, 'failed')) {
                return WhatsappLog::create([
                    'recipient' => $normalized,
                    'message' => $message,
                    'type' => $type,
                    'status' => 'failed',
                    'error_message' => 'Simulated WhatsApp Gateway delivery failure (Sandbox Mode).',
                    'payload' => json_encode(['simulation' => true, 'target' => $normalized]),
                ]);
            }

            return WhatsappLog::create([
                'recipient' => $normalized,
                'message' => $message,
                'type' => $type,
                'status' => 'sent',
                'payload' => json_encode(['simulation' => true, 'target' => $normalized]),
            ]);
        }

        // 3. Active production sending via Fonnte API
        $token = Setting::get('whatsapp_api_token') ?: config('services.whatsapp.token');

        if (! $token) {
            return WhatsappLog::create([
                'recipient' => $normalized,
                'message' => $message,
                'type' => $type,
                'status' => 'failed',
                'error_message' => 'API Token is missing in system settings.',
                'payload' => json_encode(['error' => 'No Token Provided']),
            ]);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $normalized,
                'message' => $message,
            ]);

            $body = $response->json();

            if ($response->successful() && isset($body['status']) && $body['status'] == true) {
                return WhatsappLog::create([
                    'recipient' => $normalized,
                    'message' => $message,
                    'type' => $type,
                    'status' => 'sent',
                    'payload' => json_encode($body),
                ]);
            } else {
                $err = $body['reason'] ?? 'Unknown error response from Fonnte API.';

                return WhatsappLog::create([
                    'recipient' => $normalized,
                    'message' => $message,
                    'type' => $type,
                    'status' => 'failed',
                    'error_message' => $err,
                    'payload' => json_encode($body),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp Gateway connection failed: '.$e->getMessage());

            return WhatsappLog::create([
                'recipient' => $normalized,
                'message' => $message,
                'type' => $type,
                'status' => 'failed',
                'error_message' => 'HTTP Exception: '.$e->getMessage(),
                'payload' => json_encode(['exception' => $e->getMessage()]),
            ]);
        }
    }

    /**
     * Normalize phone numbers to international format (62xxxxxxxx)
     */
    public function normalizePhoneNumber(string $phone): string
    {
        // Remove all non-digits
        $cleaned = preg_replace('/\D/', '', $phone);

        // If it starts with "0", replace with "62"
        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62'.substr($cleaned, 1);
        }

        // If it starts with "8" (common in ID), prepend with "62"
        if (str_starts_with($cleaned, '8')) {
            $cleaned = '62'.$cleaned;
        }

        return $cleaned;
    }
}
