<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\HumanCapital\Evaluation360Service;
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

    }
}
