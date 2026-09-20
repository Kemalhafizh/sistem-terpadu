<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Evaluation360Cycle;
use App\Models\Evaluation360Instrument;
use App\Models\FacilityAsset;
use App\Models\OsisCandidate;
use App\Models\OsisElection;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class EvaluationMobileDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstWhere('role', 'admin') ?? User::factory()->create(['role' => 'admin', 'name' => 'Admin Eval']);
        
        $students = Student::take(3)->get();
        if ($students->count() < 2) {
            $students = Student::factory()->count(2)->create();
        }

        // 1. Setup Evaluasi Guru (360 Cycle)
        $cycle = Evaluation360Cycle::updateOrCreate(
            ['title' => 'Evaluasi Kinerja Guru Semester Ganjil 2026'],
            [
                'academic_year' => '2026/2027',
                'semester' => 'Ganjil',
                'status' => 'active',
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(15),
                'created_by' => $admin->id,
            ]
        );

        Evaluation360Instrument::updateOrCreate(
            ['cycle_id' => $cycle->id, 'evaluator_type' => 'student'],
            [
                'questions' => [
                    'Guru menjelaskan materi dengan sangat jelas dan mudah dipahami.',
                    'Guru memberikan kesempatan bertanya dan berdiskusi.',
                    'Guru memberikan feedback tugas secara konstruktif.'
                ],
                'scale_type' => 'likert5',
                'is_anonymous' => true,
            ]
        );

        // 2. Setup Fasilitas untuk disurvey
        $facilities = [
            ['name' => 'Laboratorium Komputer Utama', 'category' => 'Lab', 'location' => 'Gedung B Lt 2', 'asset_tag' => 'LAB-KOMP-01'],
            ['name' => 'Perpustakaan Digital', 'category' => 'Ruang Publik', 'location' => 'Gedung A Lt 1', 'asset_tag' => 'PERPUS-01'],
            ['name' => 'Lapangan Basket Indoor', 'category' => 'Olahraga', 'location' => 'Gedung C', 'asset_tag' => 'LAP-BSK-01'],
        ];

        foreach ($facilities as $fac) {
            FacilityAsset::updateOrCreate(['asset_tag' => $fac['asset_tag']], $fac);
        }

        // 3. Setup E-Voting OSIS
        $academicYear = AcademicYear::first() ?? AcademicYear::factory()->create(['is_active' => true]);

        $election = OsisElection::updateOrCreate(
            ['title' => 'Pemilihan Ketua OSIS Masa Bakti 2026/2027'],
            [
                'academic_year_id' => $academicYear->id,
                'voting_start' => now()->subDay(),
                'voting_end' => now()->addDays(3),
                'status' => 'active',
            ]
        );

        OsisCandidate::updateOrCreate(
            ['election_id' => $election->id, 'candidate_number' => 1],
            [
                'student_id' => $students[0]->id,
                'vision' => 'Mewujudkan OSIS yang Kreatif, Inovatif, dan Berwawasan Global.',
                'mission' => "1. Menyelenggarakan event skala nasional\n2. Mendorong prestasi akademik & non-akademik.",
            ]
        );

        OsisCandidate::updateOrCreate(
            ['election_id' => $election->id, 'candidate_number' => 2],
            [
                'student_id' => $students[1]->id,
                'vision' => 'Membangun Generasi Pelajar Berkarakter dan Mandiri.',
                'mission' => "1. Meningkatkan kedisiplinan siswa\n2. Memfasilitasi pengembangan minat dan bakat.",
            ]
        );
    }
}
