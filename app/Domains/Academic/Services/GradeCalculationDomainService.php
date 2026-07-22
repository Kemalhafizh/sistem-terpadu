<?php

namespace App\Domains\Academic\Services;

class GradeCalculationDomainService
{
    /**
     * Calculate final grade based on SMA ISA 1 Academic Standard.
     * Formula: (Assignment * 20%) + (Midterm * 30%) + (Final CBT * 40%) + (Attitude * 10%)
     */
    public function calculateFinalGrade(float $assignment, float $midterm, float $cbt, float $attitude = 85.0): array
    {
        $finalScore = ($assignment * 0.20) + ($midterm * 0.30) + ($cbt * 0.40) + ($attitude * 0.10);
        $finalScore = round($finalScore, 2);

        $predicate = match (true) {
            $finalScore >= 90 => 'A (Sangat Baik)',
            $finalScore >= 80 => 'B (Baik)',
            $finalScore >= 70 => 'C (Cukup)',
            $finalScore >= 60 => 'D (Kurang)',
            default => 'E (Sangat Kurang)',
        };

        $isPassed = $finalScore >= 75.0; // KKM SMA ISA 1

        return [
            'final_score' => $finalScore,
            'predicate' => $predicate,
            'is_passed' => $isPassed,
            'kkm' => 75.0,
        ];
    }
}
