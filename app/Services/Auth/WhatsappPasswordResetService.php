<?php

namespace App\Services\Auth;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\User;
use App\Services\WhatsappService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class WhatsappPasswordResetService
{
    public const OTP_TTL_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    public function __construct(
        protected UserIdentifierResolver $identifierResolver,
        protected WhatsappService $whatsappService
    ) {}

    /**
     * Check whether self-service WA OTP reset is genuinely configured and allowed.
     */
    public function isAvailable(): bool
    {
        if ((string) Setting::get('whatsapp_otp_enabled') === '1') {
            return true;
        }

        $sandboxSetting = Setting::get('whatsapp_sandbox_mode');
        $isSandbox = $sandboxSetting !== null
            ? (string) $sandboxSetting === '1'
            : (bool) config('services.whatsapp.sandbox', true);

        $token = Setting::get('whatsapp_api_token') ?: config('services.whatsapp.token');

        return ! $isSandbox && filled($token);
    }

    /**
     * @return array{phone_masked: string, expires_in_minutes: int}
     */
    public function requestOtp(string $identifier): array
    {
        if (! $this->isAvailable()) {
            throw new \DomainException(__('Layanan OTP WhatsApp belum aktif. Hubungi Wali Kelas atau Tata Usaha untuk reset password.'));
        }

        $user = $this->identifierResolver->findByIdentifier($identifier);
        if (! $user || blank($user->phone_number)) {
            throw new \DomainException(__('Akun dengan nomor WhatsApp terdaftar tidak ditemukan. Hubungi Wali Kelas atau Tata Usaha.'));
        }

        if (($user->account_status ?? 'aktif') === 'nonaktif') {
            throw new \DomainException(__('Akun Anda sedang nonaktif. Hubungi Tata Usaha sekolah.'));
        }

        $throttleKey = $this->throttleKey($user->id);
        $sentCount = (int) Cache::get($throttleKey, 0);
        if ($sentCount >= 3) {
            throw new \DomainException(__('Permintaan OTP terlalu sering. Silakan tunggu 15 menit lagi.'));
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $appName = function_exists('school_name') ? school_name() : 'Sulaone';
        $message = "Kode OTP reset kata sandi *{$appName}* Anda: *{$otp}*.\n"
            .'Berlaku selama '.self::OTP_TTL_MINUTES.' menit. Jangan berikan kode ini kepada siapa pun.';

        $log = $this->whatsappService->sendMessage((string) $user->phone_number, $message, 'password_reset_otp');
        if ($log->status !== 'sent') {
            throw new \RuntimeException(__('Gagal mengirim OTP ke WhatsApp: :reason', [
                'reason' => $log->error_message ?: 'Gateway error',
            ]));
        }

        Cache::put($this->otpCacheKey($user->id), [
            'hash' => Hash::make($otp),
            'attempts' => 0,
        ], now()->addMinutes(self::OTP_TTL_MINUTES));

        Cache::put($throttleKey, $sentCount + 1, now()->addMinutes(15));

        return [
            'phone_masked' => $this->maskPhone((string) $user->phone_number),
            'expires_in_minutes' => self::OTP_TTL_MINUTES,
        ];
    }

    public function resetWithOtp(string $identifier, string $otp, string $newPassword): User
    {
        if (! $this->isAvailable()) {
            throw new \DomainException(__('Layanan OTP WhatsApp belum aktif.'));
        }

        $user = $this->identifierResolver->findByIdentifier($identifier);
        if (! $user) {
            throw new \DomainException(__('Akun tidak ditemukan atau kode OTP tidak valid.'));
        }

        $cacheKey = $this->otpCacheKey($user->id);
        $payload = Cache::get($cacheKey);
        if (! is_array($payload) || empty($payload['hash'])) {
            throw new \DomainException(__('Kode OTP sudah kedaluwarsa atau belum diminta.'));
        }

        if (! Hash::check(trim($otp), (string) $payload['hash'])) {
            $attempts = ((int) ($payload['attempts'] ?? 0)) + 1;
            if ($attempts >= self::MAX_ATTEMPTS) {
                Cache::forget($cacheKey);
                throw new \DomainException(__('Kode OTP salah terlalu banyak kali. Silakan minta kode OTP baru.'));
            }

            $payload['attempts'] = $attempts;
            Cache::put($cacheKey, $payload, now()->addMinutes(self::OTP_TTL_MINUTES));
            throw new \DomainException(__('Kode OTP tidak sesuai.'));
        }

        Cache::forget($cacheKey);

        $user->forceFill([
            'password' => Hash::make($newPassword),
            'must_change_password' => false,
        ])->save();

        $user->tokens()->delete();

        ActivityLog::create([
            'user_id' => $user->id,
            'event' => 'password_reset_via_whatsapp_otp',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'new_values' => ['method' => 'whatsapp_otp'],
        ]);

        return $user->fresh();
    }

    private function otpCacheKey(int $userId): string
    {
        return "wa_password_otp:{$userId}";
    }

    private function throttleKey(int $userId): string
    {
        return "wa_password_otp_rate:{$userId}";
    }

    private function maskPhone(string $phone): string
    {
        $len = strlen($phone);
        if ($len <= 5) {
            return str_repeat('*', $len);
        }

        return substr($phone, 0, 4).str_repeat('*', max(2, $len - 7)).substr($phone, -3);
    }
}
