<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\AlumniProfile;
use App\Models\CrowdfundCampaign;
use App\Models\AlumniAchievement;
use App\Models\LegacyPledge;
use App\Models\Student;
use App\Models\User;

class AlumniRelationsSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();
        $student = Student::first();
        if (!$student) {
            $studentId = DB::table('students')->insertGetId([
                'nis' => '12345678',
                'name' => 'Budi Santoso',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $studentId = $student->id;
        }

        $profile = AlumniProfile::updateOrCreate(
            ['student_id' => $studentId],
            [
                'graduation_year' => '2015',
                'current_company' => 'Tech Corp',
                'job_title' => 'Software Engineer',
                'industry' => 'Technology',
                'linkedin_url' => 'https://linkedin.com/in/budisantoso',
                'is_willing_to_mentor' => true,
            ]
        );

        CrowdfundCampaign::create([
            'title' => 'Pembangunan Masjid Sekolah',
            'description' => 'Kampanye donasi untuk pembangunan masjid SMA Islam Sultan Agung 1 Semarang.',
            'target_amount' => 500000000,
            'current_amount' => 150000000,
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        AlumniAchievement::create([
            'alumni_profile_id' => $profile->id,
            'achievement_title' => 'Penghargaan Inovasi Teknologi 2025',
            'category' => 'professional',
            'date_achieved' => now()->subYear(),
            'description' => 'Menerima penghargaan atas inovasi di bidang AI.',
            'is_featured_in_hall_of_fame' => true,
        ]);

        LegacyPledge::create([
            'donor_name' => 'Keluarga Bapak Budi Santoso',
            'donor_type' => 'Alumni',
            'pledged_amount' => 50000000,
            'fund_designation' => 'Beasiswa Pendidikan',
            'expected_fulfillment_date' => now()->addYear(),
            'status' => 'pledged',
        ]);
    }
}
