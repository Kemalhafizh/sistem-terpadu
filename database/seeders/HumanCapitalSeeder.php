<?php

namespace Database\Seeders;

use App\Models\CpdCategory;
use App\Models\User;
use App\Services\HumanCapital\CpdTrackerService;
use App\Services\HumanCapital\Evaluation360Service;
use App\Services\HumanCapital\SuccessionPlanningService;
use App\Services\HumanCapital\WorkloadBalancingService;
use Illuminate\Database\Seeder;

class HumanCapitalSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::where('role', 'superadmin')->first() ?? User::factory()->create(['role' => 'superadmin']);
        $teacher = User::where('role', 'guru')->first() ?? User::factory()->create(['role' => 'guru']);
        $student = User::where('role', 'student')->first() ?? User::factory()->create(['role' => 'student']);

        // 1. Evaluasi 360 & EDOM
        $evalService = app(Evaluation360Service::class);
        $cycle = $evalService->createCycle([
            'title' => 'Siklus Evaluasi Kinerja Guru & EDOM Semester Ganjil 2026/2027',
            'academic_year' => '2026/2027',
        ], $superadmin->id);

        $instSupervisor = $evalService->createInstrument($cycle, ['evaluator_type' => 'supervisor']);
        $instStudent = $evalService->createInstrument($cycle, ['evaluator_type' => 'student']);

        $evalService->submitResponse($instSupervisor, $teacher->id, $superadmin->id, [4, 5, 4, 5]);
        $evalService->submitResponse($instStudent, $teacher->id, $student->id, [5, 5, 4, 5]);

        $evalService->generateSummary($cycle->id, $teacher->id);

        // 2. CPD / PKB Categories & Activity
        $categories = [
            ['name' => 'Pengembangan Diri (Diklat / Workshop)', 'description' => 'Pelatihan fungsional guru', 'credit_weight' => 1.0],
            ['name' => 'Publikasi Ilmiah', 'description' => 'Penulisan jurnal & buku teks', 'credit_weight' => 2.0],
            ['name' => 'Karya Inovatif', 'description' => 'Pembuatan alat peraga / media digital', 'credit_weight' => 1.5],
            ['name' => 'Sertifikasi Profesi', 'description' => 'Sertifikasi keahlian pendidik', 'credit_weight' => 3.0],
        ];

        foreach ($categories as $cat) {
            CpdCategory::updateOrCreate(['name' => $cat['name']], $cat);
        }

        $cpdCategory = CpdCategory::first();
        if ($cpdCategory) {
            $cpdService = app(CpdTrackerService::class);
            $act = $cpdService->logActivity([
                'cpd_category_id' => $cpdCategory->id,
                'title' => 'Workshop Nasional Implementasi Pembelajaran Kurikulum Merdeka',
                'description' => 'Pelatihan fungsional 32 JP oleh Kemendikbudristek',
                'hours' => 32,
                'credits_earned' => 4.0,
            ], $teacher->id);

            $cpdService->verifyActivity($act, $superadmin->id, true);
        }

        // 3. Leadership Succession Planning
        $succService = app(SuccessionPlanningService::class);
        $pos = $succService->identifyPosition('waka_kurikulum', $teacher->id);

        $candidateTeacher = User::where('role', 'guru')->where('id', '!=', $teacher->id)->first() ?? User::factory()->create(['role' => 'guru']);
        $succService->addCandidate($pos, $candidateTeacher->id, 'ready_1yr', ['Disiplin Tinggi', 'Keahlian Kurikulum Merdeka'], ['Manajemen Anggaran'], $superadmin->id);

        // 4. Workload Balancing
        $workloadService = app(WorkloadBalancingService::class);
        $workloadService->calculateWorkload($teacher->id, 24, ['Wali Kelas X MIPA 1'], ['Pembina Ekstra Karya Ilmiah Remaja']);
    }
}
