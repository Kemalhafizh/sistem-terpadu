<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class TotpSecurityService
{
    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate 16-character Base32 TOTP secret key.
     */
    public function generateSecret(int $length = 16): string
    {
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= self::BASE32_CHARS[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Generate OTPAuth URL compatible with Google Authenticator / Authy / Microsoft Authenticator.
     */
    public function generateQrCodeUrl(string $email, string $secret, string $issuer = 'SMA Islam Sultan Agung 1'): string
    {
        $encodedIssuer = rawurlencode($issuer);
        $encodedEmail = rawurlencode($email);

        return "otpauth://totp/{$encodedIssuer}:{$encodedEmail}?secret={$secret}&issuer={$encodedIssuer}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * Generate array of emergency recovery codes.
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(Str::random(4) . '-' . Str::random(4));
        }
        return $codes;
    }

    /**
     * Verify 6-digit TOTP code against secret key using RFC 6238 algorithm (+/- 1 time step drift).
     */
    public function verifyCode(string $secret, string $code, int $discretion = 1): bool
    {
        $code = trim($code);
        if (strlen($code) !== 6 || ! ctype_digit($code)) {
            return false;
        }

        $timeStep = 30;
        $currentTime = floor(time() / $timeStep);
        $binarySecret = $this->base32Decode($secret);

        for ($i = -$discretion; $i <= $discretion; $i++) {
            $timeCounter = $currentTime + $i;
            $packedTime = pack('N*', 0) . pack('N*', $timeCounter);
            $hash = hash_hmac('sha1', $packedTime, $binarySecret, true);

            $offset = ord($hash[19]) & 0xf;
            $otp = (
                ((ord($hash[$offset]) & 0x7f) << 24) |
                ((ord($hash[$offset + 1]) & 0xff) << 16) |
                ((ord($hash[$offset + 2]) & 0xff) << 8) |
                (ord($hash[$offset + 3]) & 0xff)
            ) % 1000000;

            $formattedOtp = str_pad((string) $otp, 6, '0', STR_PAD_LEFT);

            if (hash_equals($formattedOtp, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify code against user TOTP secret or emergency recovery codes.
     */
    public function verifyUserTotpOrRecoveryCode(User $user, string $code): bool
    {
        $code = strtoupper(trim($code));

        if (! $user->hasTotpEnabled()) {
            return false;
        }

        // Try TOTP 6-digit verification
        if (strlen($code) === 6 && ctype_digit($code)) {
            if ($this->verifyCode($user->totp_secret, $code)) {
                return true;
            }
        }

        // Try Recovery Code verification
        $recoveryCodes = $user->totp_recovery_codes ?? [];
        if (in_array($code, $recoveryCodes)) {
            // Remove used recovery code
            $updatedCodes = array_values(array_filter($recoveryCodes, fn ($c) => $c !== $code));
            $user->totp_recovery_codes = $updatedCodes;
            $user->save();

            return true;
        }

        return false;
    }

    /**
     * Helper to decode Base32 string to binary.
     */
    private function base32Decode(string $base32): string
    {
        $base32 = strtoupper($base32);
        $binary = '';
        $buffer = 0;
        $bufferSize = 0;

        for ($i = 0; $i < strlen($base32); $i++) {
            $char = $base32[$i];
            $pos = strpos(self::BASE32_CHARS, $char);

            if ($pos === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $pos;
            $bufferSize += 5;

            if ($bufferSize >= 8) {
                $bufferSize -= 8;
                $binary .= chr(($buffer >> $bufferSize) & 0xff);
            }
        }

        return $binary;
    }
}
