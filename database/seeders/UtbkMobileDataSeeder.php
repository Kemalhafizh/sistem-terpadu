<?php

namespace Database\Seeders;

use App\Models\PtnProgram;
use App\Models\Tryout;
use App\Models\TryoutQuestion;
use Illuminate\Database\Seeder;

class UtbkMobileDataSeeder extends Seeder
{
    public function run(): void
    {
        $tryout = Tryout::updateOrCreate(
            ['title' => 'Tryout Akbar SNBT Nasional 2026'],
            [
                'description' => 'Simulasi ujian tertulis berbasis komputer (UTBK) 2026 tingkat nasional.',
                'start_time' => now()->subDay(),
                'end_time' => now()->addDays(7),
                'duration_minutes' => 195,
                'status' => 'active',
            ]
        );

        // Seed Questions TPS
        TryoutQuestion::updateOrCreate(
            ['tryout_id' => $tryout->id, 'question_text' => 'Jika x = 5 dan y = 3, maka nilai dari x^2 - y^2 adalah...'],
            [
                'sub_test' => 'TPS',
                'options' => ['A' => '16', 'B' => '8', 'C' => '4', 'D' => '2', 'E' => '0'],
                'correct_answer' => 'A',
                'difficulty_b' => -1.5,
                'discrimination_a' => 1.2,
            ]
        );

        TryoutQuestion::updateOrCreate(
            ['tryout_id' => $tryout->id, 'question_text' => 'Antonim dari kata "KENDAL" adalah...'],
            [
                'sub_test' => 'TPS',
                'options' => ['A' => 'Lancar', 'B' => 'Tersendat', 'C' => 'Halangan', 'D' => 'Rintangan', 'E' => 'Mulus'],
                'correct_answer' => 'A',
                'difficulty_b' => 0.5,
                'discrimination_a' => 0.8,
            ]
        );

        // Seed PTN Programs
        $ptns = [
            ['university_name' => 'Universitas Indonesia', 'program_name' => 'Pendidikan Dokter', 'cluster' => 'Saintek', 'passing_grade' => 745.5, 'capacity' => 60],
            ['university_name' => 'Universitas Gadjah Mada', 'program_name' => 'Ilmu Komputer', 'cluster' => 'Saintek', 'passing_grade' => 710.0, 'capacity' => 80],
            ['university_name' => 'Institut Teknologi Bandung', 'program_name' => 'STEI', 'cluster' => 'Saintek', 'passing_grade' => 735.0, 'capacity' => 120],
            ['university_name' => 'Universitas Diponegoro', 'program_name' => 'Hukum', 'cluster' => 'Soshum', 'passing_grade' => 650.0, 'capacity' => 200],
            ['university_name' => 'Universitas Brawijaya', 'program_name' => 'Akuntansi', 'cluster' => 'Soshum', 'passing_grade' => 620.5, 'capacity' => 150],
        ];

        foreach ($ptns as $ptn) {
            PtnProgram::updateOrCreate(
                ['university_name' => $ptn['university_name'], 'program_name' => $ptn['program_name']],
                $ptn
            );
        }
    }
}
