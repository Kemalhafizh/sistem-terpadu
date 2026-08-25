<?php

namespace Database\Seeders;

use App\Models\MobileAppConfig;
use Illuminate\Database\Seeder;

class MobileAppConfigSeeder extends Seeder
{
    public function run(): void
    {
        MobileAppConfig::updateOrCreate(
            ['id' => 1],
            [
                'app_name' => 'SISTA SMA Islam Sultan Agung 1',
                'min_android_build' => 100,
                'latest_android_build' => 105,
                'min_ios_build' => 100,
                'latest_ios_build' => 105,
                'force_update' => false,
                'maintenance_mode' => false,
                'maintenance_message' => 'Sistem sedang dalam optimalisasi server.',
                'play_store_url' => 'https://play.google.com/store/apps/details?id=sch.id.sultanagung1.sista',
                'app_store_url' => 'https://apps.apple.com/app/sista-sultan-agung-1/id6400000000',
                'active_features' => [
                    'academic_grades' => true,
                    'attendance_gps' => true,
                    'spp_payment_gateway' => true,
                    'tahfidz_quran' => true,
                    'cbt_exam_mode' => true,
                    'biometric_login' => true,
                    'offline_sync' => true,
                    'dark_mode' => true,
                    'family_collaboration' => true,
                    'green_school' => true,
                ],
            ]
        );
    }
}
