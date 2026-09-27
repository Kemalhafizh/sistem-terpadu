<?php

namespace App\Services\Grc;

use App\Models\AccreditationEvidence;
use App\Models\AccreditationIndicator;
use App\Models\AccreditationStandard;
use Illuminate\Support\Collection;

class AccreditationService
{
    /**
     * Calculate readiness score for a specific 8 SNP standard.
     */
    public function calculateStandardScore(int $standardId): array
    {
        $standard = AccreditationStandard::with('indicators.evidences')->findOrFail($standardId);
        $indicators = $standard->indicators;

        if ($indicators->isEmpty()) {
            return [
                'standard_id' => $standardId,
                'code' => $standard->code,
                'name' => $standard->standard_name,
                'total_indicators' => 0,
                'verified_count' => 0,
                'score_percent' => 0.0,
            ];
        }

        $verifiedIndicatorsCount = 0;
        foreach ($indicators as $ind) {
            $hasVerifiedEvidence = $ind->evidences->contains(fn ($ev) => $ev->isVerified());
            if ($hasVerifiedEvidence) {
                $verifiedIndicatorsCount++;
            }
        }

        $scorePercent = round(($verifiedIndicatorsCount / $indicators->count()) * 100, 1);

        return [
            'standard_id' => $standardId,
            'code' => $standard->code,
            'name' => $standard->standard_name,
            'total_indicators' => $indicators->count(),
            'verified_count' => $verifiedIndicatorsCount,
            'score_percent' => $scorePercent,
        ];
    }

    /**
     * Calculate overall BAN-SM accreditation readiness score across all 8 SNP standards.
     */
    public function calculateOverallScore(): array
    {
        $standards = AccreditationStandard::all();
        if ($standards->isEmpty()) {
            return [
                'overall_score' => 0.0,
                'grade' => 'Belum Terakreditasi',
                'standards_breakdown' => [],
            ];
        }

        $breakdown = [];
        $totalWeightedScore = 0.0;
        $totalWeight = 0.0;

        foreach ($standards as $std) {
            $calc = $this->calculateStandardScore($std->id);
            $breakdown[] = $calc;
            $totalWeightedScore += ($calc['score_percent'] * $std->weight);
            $totalWeight += $std->weight;
        }

        $overallScore = $totalWeight > 0 ? round($totalWeightedScore / $totalWeight, 1) : 0.0;

        $grade = match (true) {
            $overallScore >= 91.0 => 'Terakreditasi A (Unggul)',
            $overallScore >= 81.0 => 'Terakreditasi B (Baik)',
            $overallScore >= 71.0 => 'Terakreditasi C (Cukup)',
            default => 'Belum Memenuhi Syarat Minimal',
        };

        return [
            'overall_score' => $overallScore,
            'grade' => $grade,
            'standards_breakdown' => $breakdown,
        ];
    }

    /**
     * Get overall readiness percentage.
     */
    public function getReadinessPercentage(): float
    {
        return $this->calculateOverallScore()['overall_score'];
    }

    /**
     * Upload physical / digital evidence for an accreditation indicator.
     */
    public function uploadEvidence(array $data, int $uploadedBy): AccreditationEvidence
    {
        return AccreditationEvidence::create([
            'accreditation_indicator_id' => $data['accreditation_indicator_id'],
            'evidence_type' => $data['evidence_type'] ?? 'document',
            'file_path' => $data['file_path'],
            'description' => $data['description'] ?? null,
            'academic_year' => $data['academic_year'] ?? '2025/2026',
            'uploaded_by' => $uploadedBy,
            'verified_by' => null,
            'verified_at' => null,
        ]);
    }

    /**
     * Approve & verify evidence submission.
     */
    public function verifyEvidence(int $evidenceId, int $verifiedBy): AccreditationEvidence
    {
        $evidence = AccreditationEvidence::findOrFail($evidenceId);
        $evidence->verified_by = $verifiedBy;
        $evidence->verified_at = now();
        $evidence->save();

        return $evidence;
    }

    /**
     * Generate formal BAN-SM Accreditation Readiness report data.
     */
    public function generateReport(): array
    {
        $overall = $this->calculateOverallScore();

        return [
            'institution' => school_name(),
            'npsn' => school_npsn() ?: null,
            'generated_at' => now()->format('d M Y H:i:s T'),
            'overall_readiness_score' => $overall['overall_score'],
            'accreditation_grade' => $overall['grade'],
            'snp_standards' => $overall['standards_breakdown'],
        ];
    }
}
