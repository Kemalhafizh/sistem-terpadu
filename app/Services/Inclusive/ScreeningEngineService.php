<?php

namespace App\Services\Inclusive;

class ScreeningEngineService
{
    public function evaluateResponses(array $responses): int
    {
        $score = 0;
        foreach ($responses as $response) {
            $score += (int) ($response['value'] ?? 0);
        }

        return $score;
    }

    public function calculateRiskLevel(int $totalScore): string
    {
        if ($totalScore > 20) {
            return 'high';
        } elseif ($totalScore > 10) {
            return 'moderate';
        }

        return 'low';
    }

    public function generateActionableRecommendations(string $riskLevel): string
    {
        return match ($riskLevel) {
            'high' => 'Rujukan segera ke profesional spesialis. Observasi intensif harian.',
            'moderate' => 'Pemantauan berkala dan penyesuaian metode pembelajaran di kelas.',
            default => 'Perkembangan sesuai ekspektasi. Lanjutkan pendekatan standar.',
        };
    }
}
