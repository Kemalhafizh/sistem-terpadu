<?php

namespace App\Services\Inclusive;

use App\Models\DifferentiatedPlan;

class InstructionalDesignService
{
    public function mapStudentsToTiers(array $studentScores): array
    {
        $tiers = ['tier_1' => [], 'tier_2' => [], 'tier_3' => []];
        foreach ($studentScores as $studentId => $score) {
            if ($score < 50) {
                $tiers['tier_3'][] = $studentId;
            } elseif ($score < 75) {
                $tiers['tier_2'][] = $studentId;
            } else {
                $tiers['tier_1'][] = $studentId;
            }
        }

        return $tiers;
    }

    public function suggestDifferentiationStrategies(string $type): array
    {
        return match ($type) {
            'content' => ['Menyediakan teks dengan tingkat kesulitan berbeda', 'Penggunaan media visual'],
            'process' => ['Pembelajaran kooperatif', 'Pemberian waktu tambahan'],
            'product' => ['Pilihan format tugas (video, poster, esai)', 'Presentasi lisan'],
            default => [],
        };
    }

    public function analyzePlanEffectiveness(DifferentiatedPlan $plan, array $studentOutcomes): float
    {
        if (empty($studentOutcomes)) {
            return 0.0;
        }

        $successCount = count(array_filter($studentOutcomes, fn ($score) => $score >= 75));

        return ($successCount / count($studentOutcomes)) * 100;
    }
}
