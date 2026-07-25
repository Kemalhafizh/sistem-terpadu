<?php

namespace Database\Seeders;

use App\Models\AccreditationStandard;
use App\Models\User;
use App\Services\Grc\QualityAssuranceService;
use Illuminate\Database\Seeder;

class QualityAssuranceSeeder extends Seeder
{
    public function run(): void
    {
        $qaService = app(QualityAssuranceService::class);
        $user = User::first();

        if (! $user) {
            return;
        }

        // Seed EDS Cycle
        $cycle = $qaService->createCycle([
            'academic_year' => '2025/2026',
            'semester' => 'genap',
            'status' => 'completed',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->toDateString(),
        ], $user->id);

        $standards = AccreditationStandard::all();

        foreach ($standards as $std) {
            $qaService->submitResponse([
                'cycle_id' => $cycle->id,
                'standard_id' => $std->id,
                'question_text' => "Evaluasi Pemenuhan Mutu {$std->standard_name}",
                'response_text' => "Pemenuhan indikator {$std->code} terlaksana dengan baik.",
                'score' => rand(3, 4),
                'evidence_notes' => 'Tersedia dokumen SK dan sertifikat mutu fisik terverifikasi.',
            ], $user->id);
        }
    }
}
