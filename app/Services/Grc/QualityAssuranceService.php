<?php

namespace App\Services\Grc;

use App\Models\AccreditationStandard;
use App\Models\SelfEvaluationCycle;
use App\Models\SelfEvaluationResponse;
use Illuminate\Support\Collection;

class QualityAssuranceService
{
    /**
     * Create new School Self-Evaluation (EDS) Cycle.
     */
    public function createCycle(array $data, int $createdBy): SelfEvaluationCycle
    {
        return SelfEvaluationCycle::create([
            'academic_year' => $data['academic_year'] ?? '2025/2026',
            'semester' => $data['semester'] ?? 'genap',
            'status' => $data['status'] ?? 'active',
            'start_date' => $data['start_date'] ?? now()->toDateString(),
            'end_date' => $data['end_date'] ?? now()->addMonth()->toDateString(),
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Submit or update self-evaluation response on 1-4 scale per standard.
     */
    public function submitResponse(array $data, int $respondentId): SelfEvaluationResponse
    {
        return SelfEvaluationResponse::updateOrCreate(
            [
                'cycle_id' => $data['cycle_id'],
                'standard_id' => $data['standard_id'],
                'respondent_id' => $respondentId,
            ],
            [
                'question_text' => $data['question_text'] ?? 'Evaluasi Pemenuhan Standar Mutu Internal',
                'response_text' => $data['response_text'] ?? null,
                'score' => max(1, min(4, (int) $data['score'])),
                'evidence_notes' => $data['evidence_notes'] ?? null,
            ]
        );
    }

    /**
     * Calculate EDS results (mean score 1.00 - 4.00) across all 8 SNP standards for a cycle.
     */
    public function calculateResults(int $cycleId): array
    {
        $cycle = SelfEvaluationCycle::findOrFail($cycleId);
        $standards = AccreditationStandard::all();

        $breakdown = [];
        $totalScores = 0;
        $responseCount = 0;

        foreach ($standards as $std) {
            $responses = SelfEvaluationResponse::where('cycle_id', $cycleId)
                ->where('standard_id', $std->id)
                ->get();

            $count = $responses->count();
            $meanScore = $count > 0 ? round($responses->avg('score'), 2) : 0.0;

            if ($count > 0) {
                $totalScores += $responses->sum('score');
                $responseCount += $count;
            }

            $breakdown[] = [
                'standard_id' => $std->id,
                'code' => $std->code,
                'name' => $std->standard_name,
                'response_count' => $count,
                'mean_score' => $meanScore,
                'rating' => $this->getScoreCategory($meanScore),
            ];
        }

        $overallMean = $responseCount > 0 ? round($totalScores / $responseCount, 2) : 0.0;

        return [
            'cycle_id' => $cycleId,
            'academic_year' => $cycle->academic_year,
            'semester' => $cycle->semester,
            'overall_mean' => $overallMean,
            'overall_category' => $this->getScoreCategory($overallMean),
            'standards_breakdown' => $breakdown,
        ];
    }

    /**
     * Generate formal School Self-Evaluation (EDS) report.
     */
    public function generateEdsReport(int $cycleId): array
    {
        $results = $this->calculateResults($cycleId);

        return [
            'institution' => 'SMA Islam Sultan Agung 1 Semarang',
            'npsn' => '20328888',
            'report_title' => "Laporan Evaluasi Diri Sekolah (EDS) — TA {$results['academic_year']} " . ucfirst($results['semester']),
            'overall_score' => $results['overall_mean'],
            'overall_rating' => $results['overall_category'],
            'standards' => $results['standards_breakdown'],
            'generated_at' => now()->format('d M Y H:i:s T'),
        ];
    }

    /**
     * Compare evaluation results between two EDS cycles for trend analysis.
     */
    public function compareWithPrevious(int $currentCycleId, ?int $previousCycleId = null): array
    {
        $currentResults = $this->calculateResults($currentCycleId);

        if (! $previousCycleId) {
            $prevCycle = SelfEvaluationCycle::where('id', '<', $currentCycleId)
                ->orderBy('id', 'desc')
                ->first();
            $previousCycleId = $prevCycle?->id;
        }

        $prevResults = $previousCycleId ? $this->calculateResults($previousCycleId) : null;

        $comparison = [];
        foreach ($currentResults['standards_breakdown'] as $currStd) {
            $prevMean = 0.0;
            if ($prevResults) {
                $match = collect($prevResults['standards_breakdown'])->firstWhere('standard_id', $currStd['standard_id']);
                $prevMean = $match['mean_score'] ?? 0.0;
            }

            $diff = round($currStd['mean_score'] - $prevMean, 2);

            $comparison[] = [
                'code' => $currStd['code'],
                'name' => $currStd['name'],
                'current_score' => $currStd['mean_score'],
                'previous_score' => $prevMean,
                'difference' => $diff,
                'trend' => $diff > 0 ? 'improving' : ($diff < 0 ? 'declining' : 'stable'),
            ];
        }

        return [
            'current_cycle' => $currentResults['academic_year'] . ' ' . ucfirst($currentResults['semester']),
            'previous_cycle' => $prevResults ? ($prevResults['academic_year'] . ' ' . ucfirst($prevResults['semester'])) : 'N/A',
            'comparison' => $comparison,
        ];
    }

    private function getScoreCategory(float $score): string
    {
        return match (true) {
            $score >= 3.50 => 'Sangat Baik (Memenuhi SNP+)',
            $score >= 3.00 => 'Baik (Memenuhi SNP)',
            $score >= 2.00 => 'Cukup (Menuju SNP)',
            $score > 0 => 'Kurang (Belum Menuju SNP)',
            default => 'Belum Dievaluasi',
        };
    }
}
