<?php

namespace Database\Seeders;

use App\Models\FeatureFlag;
use Illuminate\Database\Seeder;

class FeatureFlagSeeder extends Seeder
{
    public function run(): void
    {
        FeatureFlag::updateOrCreate(
            ['key' => 'ai_teacher_copilot'],
            [
                'name' => 'AI Teacher Copilot (RPP & CBT Generator)',
                'description' => 'Asisten kecerdasan buatan untuk membantu guru menyusun RPP Kumer & bank soal CBT otomatis.',
                'is_active' => true,
                'rollout_percentage' => 100,
                'target_roles' => ['admin', 'guru'],
            ]
        );

        FeatureFlag::updateOrCreate(
            ['key' => 'iot_asset_tracking'],
            [
                'name' => 'IoT QR Code Asset Tracking',
                'description' => 'Sistem pemantauan & pelacakan inventaris sekolah berbasis QR Code & sensor IoT.',
                'is_active' => true,
                'rollout_percentage' => 100,
                'target_roles' => ['admin', 'staf_tu'],
            ]
        );

        FeatureFlag::updateOrCreate(
            ['key' => 'experimental_student_portal'],
            [
                'name' => 'Experimental Student Portal UI v2',
                'description' => 'Desain portal siswa versi 2 dengan dashboard statistik realtime.',
                'is_active' => false,
                'rollout_percentage' => 50,
                'target_roles' => ['student'],
            ]
        );
    }
}
