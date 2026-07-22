<?php

namespace Database\Seeders;

use App\Models\BlockedIp;
use App\Models\SecurityThreatLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class SecurityThreatSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?: User::first();

        // 1. Create blocked IPs
        BlockedIp::firstOrCreate(['ip_address' => '198.51.100.44'], [
            'reason' => 'Terdeteksi serangan Brute Force Login 25x berturut-turut.',
            'blocked_by' => $admin->id,
        ]);

        BlockedIp::firstOrCreate(['ip_address' => '203.0.113.89'], [
            'reason' => 'Indikasi pemindaian port dan percobaan sql injection.',
            'blocked_by' => $admin->id,
        ]);

        // 2. Create threat logs
        $events = [
            [
                'event_type' => 'brute_force_attempt',
                'severity' => 'critical',
                'ip_address' => '198.51.100.44',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Python-urllib/3.9',
                'payload_data' => ['attempts' => 25, 'target_user' => 'admin@sista.sch.id'],
                'is_resolved' => true,
            ],
            [
                'event_type' => 'off_hours_tampering',
                'severity' => 'high',
                'ip_address' => '114.124.200.15',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
                'payload_data' => ['action' => 'UPDATE_GRADES', 'time' => '02:45 AM'],
                'is_resolved' => false,
            ],
            [
                'event_type' => 'impossible_travel',
                'severity' => 'medium',
                'ip_address' => '180.252.110.88',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X)',
                'payload_data' => ['location_a' => 'Semarang', 'location_b' => 'Frankfurt', 'time_diff_minutes' => 5],
                'is_resolved' => false,
            ],
            [
                'event_type' => 'unauthorized_escalation',
                'severity' => 'critical',
                'ip_address' => '203.0.113.89',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:109.0)',
                'payload_data' => ['attempted_role' => 'superadmin', 'current_role' => 'student'],
                'is_resolved' => false,
            ],
            [
                'event_type' => 'suspicious_file_upload',
                'severity' => 'low',
                'ip_address' => '36.88.10.12',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'payload_data' => ['file_name' => 'shell.php.png', 'mime_type' => 'text/x-php'],
                'is_resolved' => true,
            ],
        ];

        foreach ($events as $evt) {
            SecurityThreatLog::create(array_merge($evt, [
                'user_id' => $admin->id,
            ]));
        }
    }
}
