<?php

namespace App\Services;

use App\Models\Registration;
use App\Models\SpmbWave;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SpmbService
{
    /**
     * Calculate registration final score based on track weights.
     */
    public function calculateApplicantScore(Registration $registration): float
    {
        $track = $registration->relationLoaded('spmbTrack') ? $registration->spmbTrack : $registration->load('spmbTrack')->spmbTrack;
        if (! $track) {
            return 0.0;
        }

        // Get weights
        $weights = $track->scoring_weights ?: [
            'rapor' => 40,
            'tes' => 30,
            'prestasi' => 15,
            'tahfidz' => 15,
        ];

        // 1. Rapor Score (Average)
        $raporAvg = 0.0;
        if ($registration->rapor_scores && is_array($registration->rapor_scores)) {
            $scores = array_filter($registration->rapor_scores, 'is_numeric');
            if (count($scores) > 0) {
                $raporAvg = array_sum($scores) / count($scores);
            }
        }

        // 2. Test Score
        $testScore = (float) ($registration->entrance_test_score ?: 0.0);

        // 3. Achievement Score
        $achievementScore = 0.0;
        if ($registration->achievement_data && is_array($registration->achievement_data)) {
            $maxScore = 0.0;
            foreach ($registration->achievement_data as $achievement) {
                $level = strtolower($achievement['level'] ?? '');
                $score = match ($level) {
                    'internasional' => 100.0,
                    'nasional' => 95.0,
                    'provinsi' => 85.0,
                    'kota' => 75.0,
                    'sekolah' => 60.0,
                    default => 50.0
                };
                if ($score > $maxScore) {
                    $maxScore = $score;
                }
            }
            $achievementScore = $maxScore;
        }

        // 4. Tahfidz Score
        $tahfidzScore = 0.0;
        if ($registration->tahfidz_data && is_array($registration->tahfidz_data)) {
            $juzCount = (int) ($registration->tahfidz_data['juz_count'] ?? 0);
            $tahfidzScore = min($juzCount * 10, 100); // 10 points per Juz, max 100
        }

        // Weighted final calculation
        $finalScore = (
            ($raporAvg * ($weights['rapor'] ?? 40)) +
            ($testScore * ($weights['tes'] ?? 30)) +
            ($achievementScore * ($weights['prestasi'] ?? 15)) +
            ($tahfidzScore * ($weights['tahfidz'] ?? 15))
        ) / 100;

        return round($finalScore, 2);
    }

    /**
     * Run selection ranking for all applicants in a wave.
     */
    public function runSelection(int $waveId): array
    {
        $wave = SpmbWave::with('period.tracks')->findOrFail($waveId);
        $period = $wave->period;
        $tracks = $period->tracks;

        $summary = [
            'total' => 0,
            'accepted' => 0,
            'rejected' => 0,
        ];

        DB::transaction(function () use ($wave, $tracks, &$summary) {
            foreach ($tracks as $track) {
                $applicants = Registration::with('spmbTrack')->where('spmb_wave_id', $wave->id)
                    ->where('spmb_track_id', $track->id)
                    ->get();

                // Calculate scores
                foreach ($applicants as $applicant) {
                    $score = $this->calculateApplicantScore($applicant);
                    $applicant->total_score = $score;
                    $applicant->save();
                }

                $sortedApplicants = Registration::with('spmbTrack')->where('spmb_wave_id', $wave->id)
                    ->where('spmb_track_id', $track->id)
                    ->orderBy('total_score', 'desc')
                    ->get();

                $quota = $track->quota;
                $rank = 1;

                foreach ($sortedApplicants as $applicant) {
                    $applicant->rank = $rank;

                    if ($rank <= $quota) {
                        $applicant->status = 'accepted';
                        $summary['accepted']++;
                    } else {
                        $applicant->status = 'rejected';
                        $summary['rejected']++;
                    }

                    $applicant->save();
                    $rank++;
                    $summary['total']++;
                }
            }
        });

        return $summary;
    }

    /**
     * Batch convert accepted applicants who have re-registered to Student/User accounts.
     */
    public function bulkConvert(int $waveId): int
    {
        $applicants = Registration::where('spmb_wave_id', $waveId)
            ->where('status', 'accepted')
            ->where('has_reregistered', true)
            ->whereNull('user_id')
            ->get();

        $count = 0;
        $regService = new RegistrationService;

        foreach ($applicants as $applicant) {
            try {
                $regService->convertToStudent($applicant);
                $count++;
            } catch (\Exception $e) {
                Log::error("Failed to convert applicant {$applicant->id} in bulk: ".$e->getMessage());
            }
        }

        return $count;
    }
}
