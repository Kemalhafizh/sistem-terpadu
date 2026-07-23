<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\TotpSecurityService;
use Illuminate\Database\Seeder;

class TwoFactorSecuritySeeder extends Seeder
{
    public function run(): void
    {
        /** @var TotpSecurityService $totpService */
        $totpService = app(TotpSecurityService::class);

        $superadmin = User::where('role', 'superadmin')->first();
        if ($superadmin && ! $superadmin->hasTotpEnabled()) {
            $secret = $totpService->generateSecret();
            $recoveryCodes = $totpService->generateRecoveryCodes();

            $superadmin->totp_secret = $secret;
            $superadmin->totp_enabled_at = now();
            $superadmin->totp_recovery_codes = $recoveryCodes;
            $superadmin->save();
        }
    }
}
