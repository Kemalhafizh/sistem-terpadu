<?php

namespace App\Domains\Keislaman\Services;

class TahfidzDomainService
{
    /**
     * Calculate Quran Memorization percentage & predicate for SMA ISA 1 student.
     */
    public function evaluateProgress(int $totalAyahMemorized, int $targetJuz = 3): array
    {
        $totalAyahPerJuz = 200; // Approximate average per Juz
        $targetAyah = $targetJuz * $totalAyahPerJuz;

        $percentage = min(100.0, round(($totalAyahMemorized / max(1, $targetAyah)) * 100, 2));

        $predicate = match (true) {
            $percentage >= 100 => 'Mumtaz (Lulus Target)',
            $percentage >= 75 => 'Jayyid Jiddan (Sangat Baik)',
            $percentage >= 50 => 'Jayyid (Baik)',
            default => 'Maqbul (Dalam Proses)',
        };

        return [
            'total_ayah' => $totalAyahMemorized,
            'target_juz' => $targetJuz,
            'percentage' => $percentage,
            'predicate' => $predicate,
        ];
    }
}
