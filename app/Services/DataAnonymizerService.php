<?php

namespace App\Services;

use App\Models\User;
use Exception;

class DataAnonymizerService
{
    /**
     * Mask NIK according to UU PDP No. 27/2022 rules.
     * Example: 3374012304950001 -> 3374**********01
     */
    public function maskNik(string $nik): string
    {
        $len = strlen($nik);
        if ($len < 6) {
            return str_repeat('*', $len);
        }

        $prefix = substr($nik, 0, 4);
        $suffix = substr($nik, -2);
        $maskedLength = max(0, $len - 6);

        return $prefix . str_repeat('*', $maskedLength) . $suffix;
    }

    /**
     * Mask Phone number according to UU PDP rules.
     * Example: 081234567890 -> 0812****7890
     */
    public function maskPhone(string $phone): string
    {
        $len = strlen($phone);
        if ($len < 8) {
            return str_repeat('*', $len);
        }

        $prefix = substr($phone, 0, 4);
        $suffix = substr($phone, -4);
        $maskedLength = max(0, $len - 8);

        return $prefix . str_repeat('*', $maskedLength) . $suffix;
    }

    /**
     * Mask Email address according to UU PDP rules.
     * Example: kemalhafizh@gmail.com -> k***h@gmail.com
     */
    public function maskEmail(string $email): string
    {
        if (! str_contains($email, '@')) {
            return '***@***.***';
        }

        [$username, $domain] = explode('@', $email, 2);
        $len = strlen($username);

        if ($len <= 2) {
            $maskedUsername = substr($username, 0, 1) . '*';
        } else {
            $firstChar = substr($username, 0, 1);
            $lastChar = substr($username, -1);
            $maskedUsername = $firstChar . str_repeat('*', 3) . $lastChar;
        }

        return $maskedUsername . '@' . $domain;
    }

    /**
     * Anonymize array of student data for export analytics.
     */
    public function anonymizeData(array $data): array
    {
        if (isset($data['nik'])) {
            $data['nik'] = $this->maskNik((string) $data['nik']);
        }
        if (isset($data['phone'])) {
            $data['phone'] = $this->maskPhone((string) $data['phone']);
        }
        if (isset($data['email'])) {
            $data['email'] = $this->maskEmail((string) $data['email']);
        }

        return $data;
    }

    /**
     * Execute "Right to be Forgotten" protocol under UU PDP No. 27/2022.
     */
    public function executeRightToBeForgotten(User $user): bool
    {
        try {
            $anonId = $user->id;

            $user->name = "ANONYMIZED_USER_#{$anonId}";
            $user->email = "anonymized_{$anonId}@privacy.local";
            $user->email_verified_at = null;

            return $user->save();
        } catch (Exception $e) {
            return false;
        }
    }
}
