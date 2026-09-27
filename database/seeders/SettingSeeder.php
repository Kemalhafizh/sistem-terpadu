<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General — the school's identity (name, address, phone, e-mail,
            // NPSN, principal) is not seeded: it comes from config/school.php
            // until an admin saves it in Admin > Profil Sekolah. This used to
            // seed "Sistem Terpadu Academy" and a Jakarta (021) number.
            ['key' => 'school_logo', 'value' => null, 'group' => 'general', 'type' => 'image'],

            // Academic
            ['key' => 'school_vision', 'value' => 'Menjadi lembaga pendidikan terdepan dalam teknologi dan karakter.', 'group' => 'academic', 'type' => 'textarea'],
            ['key' => 'school_mission', 'value' => "1. Menyelenggarakan pendidikan berkualitas.\n2. Mengintegrasikan teknologi dalam pembelajaran.\n3. Membentuk karakter islami yang kuat.", 'group' => 'academic', 'type' => 'textarea'],

            // System
            ['key' => 'app_mode', 'value' => 'development', 'group' => 'system', 'type' => 'select'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
