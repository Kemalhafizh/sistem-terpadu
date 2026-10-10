<?php

namespace Database\Seeders;

use App\Models\AlumniAchievement;
use App\Models\AlumniProfile;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlumniRelationsSeeder extends Seeder
{
    public function run(): void
    {
        $student = Student::first();
        if (! $student) {
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

        AlumniAchievement::create([
            'alumni_profile_id' => $profile->id,
            'achievement_title' => 'Penghargaan Inovasi Teknologi 2025',
            'category' => 'professional',
            'date_achieved' => now()->subYear(),
            'description' => 'Menerima penghargaan atas inovasi di bidang AI.',
            'is_featured_in_hall_of_fame' => true,
        ]);
    }
}
