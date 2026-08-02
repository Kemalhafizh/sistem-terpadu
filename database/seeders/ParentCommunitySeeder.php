<?php

namespace Database\Seeders;

use App\Models\MentorProfile;
use App\Models\MentorshipPair;
use App\Models\MobileDevice;
use App\Models\Partnership;
use App\Models\User;
use App\Models\VolunteerOpportunity;
use Illuminate\Database\Seeder;

class ParentCommunitySeeder extends Seeder
{
    public function run(): void
    {
        $parent = User::where('role', 'orang_tua')->first() ?? User::factory()->create(['role' => 'orang_tua', 'name' => 'Orang Tua Community']);
        $alumni = User::where('role', 'alumni')->first() ?? User::factory()->create(['role' => 'alumni', 'name' => 'Alumni Sukses']);
        $student = User::where('role', 'siswa')->first() ?? User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Mentee']);
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin', 'name' => 'Admin Partnership']);

        // 41.1: Mobile Device Token
        MobileDevice::create([
            'parent_user_id' => $parent->id,
            'device_token' => 'fcm-token-demo-9921',
            'platform' => 'android',
            'app_version' => '2.1.0',
            'last_active' => now(),
        ]);

        // 41.2: Partnership
        Partnership::create([
            'organization_name' => 'PT Telekomunikasi Indonesia Semarang',
            'partner_type' => 'corporate',
            'contact_person' => 'Bambang Hartono',
            'contact_email' => 'csr@telkom.co.id',
            'status' => 'active',
            'valid_until' => now()->addYears(2),
            'created_by' => $admin->id,
        ]);

        // 41.3: Volunteer Opportunity
        VolunteerOpportunity::create([
            'title' => 'Panitia Festival Budaya & Bazar Kewirausahaan Islam 2026',
            'required_volunteers' => 10,
            'required_hours' => 5,
            'event_date' => now()->addMonth(),
            'organizer_id' => $admin->id,
            'status' => 'open',
        ]);

        // 41.4: Mentor Profile & Pair
        $mp = MentorProfile::create([
            'alumni_id' => $alumni->id,
            'expertise_areas' => ['Teknik Informatika', 'UTBK-SNBT', 'Beasiswa 海外'],
            'max_mentees' => 3,
            'is_available' => true,
            'bio' => 'Software Engineer di Unicorn Tech. Siap membimbing adik-adik SMA ISA 1.',
        ]);

        MentorshipPair::create([
            'mentor_profile_id' => $mp->id,
            'student_id' => $student->id,
            'goals' => 'Lolos ITB Jurusan Teknik Informatika 2027',
            'status' => 'active',
            'started_at' => now(),
        ]);
    }
}
