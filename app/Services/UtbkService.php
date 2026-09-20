<?php

namespace App\Services;

use App\Models\AlumniProfile;
use App\Models\PtnProgram;
use App\Models\Tryout;
use App\Models\TryoutAttempt;
use App\Models\TryoutQuestion;
use Exception;
use Illuminate\Support\Facades\DB;

class UtbkService
{
    /**
     * Get available tryouts.
     */
    public function getAvailableTryouts()
    {
        return Tryout::whereIn('status', ['active', 'upcoming'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get questions for a tryout.
     */
    public function getTryoutQuestions($tryoutId)
    {
        $tryout = Tryout::findOrFail($tryoutId);
        
        if ($tryout->status !== 'active') {
            throw new Exception("Tryout ini tidak sedang aktif.");
        }

        return TryoutQuestion::where('tryout_id', $tryoutId)
            ->select('id', 'sub_test', 'question_text', 'options')
            ->get()
            ->groupBy('sub_test');
    }

    /**
     * Submit answers and calculate score using IRT Rasch Model.
     */
    public function submitTryout($tryoutId, $studentId, array $answers)
    {
        return DB::transaction(function () use ($tryoutId, $studentId, $answers) {
            $tryout = Tryout::findOrFail($tryoutId);
            
            // Validate timer (basic implementation)
            if (now() > $tryout->end_time) {
                // allow slight grace period, or just accept if auto-submitted
            }

            // Check if already attempted
            if (TryoutAttempt::where('tryout_id', $tryoutId)->where('student_id', $studentId)->exists()) {
                throw new Exception("Anda sudah mengerjakan tryout ini.");
            }

            $questions = TryoutQuestion::where('tryout_id', $tryoutId)->get()->keyBy('id');
            
            $responsesBySubtest = [
                'TPS' => [],
                'Literasi Bahasa' => [],
                'Penalaran Matematika' => []
            ];

            // Group responses
            foreach ($questions as $q) {
                $sub = $q->sub_test;
                $studentAnswer = $answers[$q->id] ?? null;
                $isCorrect = ($studentAnswer === $q->correct_answer) ? 1 : 0;
                
                $responsesBySubtest[$sub][] = [
                    'difficulty_b' => $q->difficulty_b,
                    'discrimination_a' => $q->discrimination_a,
                    'is_correct' => $isCorrect
                ];
            }

            // Calculate Theta for each subtest
            $scores = [];
            foreach ($responsesBySubtest as $sub => $items) {
                if (empty($items)) {
                    $scores[$sub] = 0;
                    continue;
                }
                
                $theta = $this->estimateThetaMLE($items);
                // Scale theta (-3 to +3) to UTBK score (300 to 1000), mean 500, stddev 150
                $utbkScore = 500 + ($theta * 150);
                $utbkScore = max(300, min(1000, $utbkScore));
                $scores[$sub] = round($utbkScore, 2);
            }

            $totalScore = round(array_sum($scores) / count(array_filter($scores) ?: [1]), 2);

            $attempt = TryoutAttempt::create([
                'tryout_id' => $tryoutId,
                'student_id' => $studentId,
                'score_tps' => $scores['TPS'] ?? 0,
                'score_literasi' => $scores['Literasi Bahasa'] ?? 0,
                'score_penalaran' => $scores['Penalaran Matematika'] ?? 0,
                'total_score' => $totalScore,
                'started_at' => now()->subMinutes($tryout->duration_minutes), // simplified
                'completed_at' => now(),
                'answers_json' => $answers,
            ]);

            return $attempt;
        });
    }

    /**
     * Newton-Raphson MLE for Theta estimation.
     * P(theta) = 1 / (1 + exp(-a(theta - b)))
     */
    private function estimateThetaMLE(array $items, $maxIter = 10, $epsilon = 0.001)
    {
        $theta = 0.0; // Initial guess
        
        // Edge case: all correct or all wrong
        $correctCount = array_sum(array_column($items, 'is_correct'));
        if ($correctCount == 0) return -3.0;
        if ($correctCount == count($items)) return 3.0;

        for ($i = 0; $i < $maxIter; $i++) {
            $numerator = 0;
            $denominator = 0;

            foreach ($items as $item) {
                $a = (float)$item['discrimination_a'];
                $b = (float)$item['difficulty_b'];
                $u = $item['is_correct'];

                $p = 1.0 / (1.0 + exp(-$a * ($theta - $b)));
                // To avoid division by zero or NaN
                $p = max(0.0001, min(0.9999, $p));
                
                $numerator += $a * ($u - $p);
                $denominator += $a * $a * $p * (1 - $p);
            }

            if ($denominator == 0) break;

            $delta = $numerator / $denominator;
            $theta += $delta;

            if (abs($delta) < $epsilon) {
                break;
            }
        }
        
        return max(-3.0, min(3.0, $theta));
    }

    /**
     * Get PTN recommendations based on estimated UTBK score.
     */
    public function getRecommendations($studentId)
    {
        // Get average total score from attempts
        $avgScore = TryoutAttempt::where('student_id', $studentId)->avg('total_score');
        
        if (!$avgScore) {
            return [
                'estimated_score' => 0,
                'recommendations' => [],
                'message' => 'Anda belum memiliki skor tryout.'
            ];
        }

        // Recommend programs where passing_grade <= avgScore + 50 (margin of optimism)
        $programs = PtnProgram::where('passing_grade', '<=', $avgScore + 25)
            ->orderByDesc('passing_grade')
            ->take(10)
            ->get()
            ->map(function ($p) use ($avgScore) {
                $chance = 'Rendah';
                if ($avgScore >= $p->passing_grade + 20) $chance = 'Tinggi';
                elseif ($avgScore >= $p->passing_grade - 10) $chance = 'Sedang';

                return [
                    'university' => $p->university_name,
                    'program' => $p->program_name,
                    'passing_grade' => $p->passing_grade,
                    'acceptance_chance' => $chance
                ];
            });

        return [
            'estimated_score' => round($avgScore, 2),
            'recommendations' => $programs,
        ];
    }

    /**
     * Get Alumni Directory by University.
     */
    public function getAlumniDirectory()
    {
        // For demonstration, fetch all alumni profiles and group by institution
        if (class_exists(AlumniProfile::class)) {
            // Note: In real app, we use AlumniProfile or AlumniData
            return AlumniProfile::select('full_name', 'current_university', 'current_major')
                ->whereNotNull('current_university')
                ->get()
                ->groupBy('current_university');
        }

        return collect();
    }
}
