<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'school_name', 'value' => 'Sistem Terpadu Academy', 'group' => 'general', 'type' => 'text'],
            ['key' => 'school_address', 'value' => 'Jl. Mataram No.657, Wonodri, Kec. Semarang Sel., Kota Semarang, Jawa Tengah 50242', 'group' => 'general', 'type' => 'textarea'],
            ['key' => 'school_phone', 'value' => '021-1234567', 'group' => 'general', 'type' => 'text'],
            ['key' => 'school_email', 'value' => 'info@sistem-terpadu.sch.id', 'group' => 'general', 'type' => 'text'],
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
