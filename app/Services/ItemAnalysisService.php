<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\QuestionBank;
use Illuminate\Support\Collection;

class ItemAnalysisService
{
    /**
     * Helper to get student answer safely supporting both question ID keys and order index keys.
     */
    private function getStudentAnswer(array $answers, $question): ?string
    {
        if (isset($answers[$question->id])) {
            return $answers[$question->id];
        }

        $order = $question->pivot->order ?? null;
        if ($order && isset($answers[$order])) {
            return $answers[$order];
        }
        if ($order && isset($answers[strval($order)])) {
            return $answers[strval($order)];
        }

        return null;
    }

    /**
     * Check if a student's answer is correct.
     */
    private function isAnswerCorrect($studentAnswer, QuestionBank $question): bool
    {
        if (is_null($studentAnswer) || $studentAnswer === '') {
            return false;
        }

        if ($question->type === 'multiple_choice') {
            return strtoupper(trim($studentAnswer)) === strtoupper(trim($question->correct_answer));
        } elseif ($question->type === 'true_false') {
            return strtolower(trim($studentAnswer)) === strtolower(trim($question->correct_answer));
        } else {
            return strtolower(trim($studentAnswer)) === strtolower(trim($question->correct_answer));
        }
    }

    /**
     * Get completed/submitted attempts for an exam.
     */
    private function getValidAttempts(int $examId): Collection
    {
        return ExamAttempt::where('exam_id', $examId)
            ->whereIn('status', ['submitted', 'graded', 'blocked_violation', 'timeout'])
            ->get();
    }

    /**
     * Hitung tingkat kesukaran (P) per soal.
     * P = jumlah yang benar / jumlah peserta.
     * P < 0.3 = sulit, P 0.3-0.7 = sedang, P > 0.7 = mudah.
     */
    public function calculateDifficultyIndex(int $examId): Collection
    {
        $exam = Exam::with('questions')->findOrFail($examId);
        $attempts = $this->getValidAttempts($examId);
        $totalParticipants = $attempts->count();

        $result = collect();

        foreach ($exam->questions as $question) {
            if ($totalParticipants === 0) {
                $p = 0.0;
            } else {
                $correctCount = 0;
                foreach ($attempts as $attempt) {
                    $answers = $attempt->answers ?? [];
                    $ans = $this->getStudentAnswer($answers, $question);
                    if ($this->isAnswerCorrect($ans, $question)) {
                        $correctCount++;
                    }
                }
                $p = $correctCount / $totalParticipants;
            }

            if ($p < 0.3) {
                $text = 'Sulit';
            } elseif ($p <= 0.7) {
                $text = 'Sedang';
            } else {
                $text = 'Mudah';
            }

            $result->put($question->id, [
                'question_id' => $question->id,
                'difficulty_index' => round($p, 2),
                'difficulty_text' => $text,
            ]);
        }

        return $result;
    }

    /**
     * Hitung daya pembeda (D) per soal.
     * D = (Ba/Ja) - (Bb/Jb) — kelompok atas vs bawah (27%).
     * D < 0.2 = jelek, D 0.2-0.4 = cukup, D > 0.4 = baik.
     */
    public function calculateDiscriminationIndex(int $examId): Collection
    {
        $exam = Exam::with('questions')->findOrFail($examId);
        $attempts = $this->getValidAttempts($examId);
        $n = $attempts->count();

        $result = collect();

        if ($n < 2) {
            foreach ($exam->questions as $question) {
                $result->put($question->id, [
                    'question_id' => $question->id,
                    'discrimination_index' => 0.0,
                    'discrimination_text' => 'Jelek',
                ]);
            }

            return $result;
        }

        // Sort attempts by score descending
        $sortedAttempts = $attempts->sortByDesc('score')->values();

        // Calculate group sizes (27%)
        $groupSize = max(1, (int) round(0.27 * $n));

        $upperGroup = $sortedAttempts->take($groupSize);
        $lowerGroup = $sortedAttempts->take(-$groupSize);

        $ja = $upperGroup->count();
        $jb = $lowerGroup->count();

        foreach ($exam->questions as $question) {
            $ba = 0;
            foreach ($upperGroup as $attempt) {
                $answers = $attempt->answers ?? [];
                $ans = $this->getStudentAnswer($answers, $question);
                if ($this->isAnswerCorrect($ans, $question)) {
                    $ba++;
                }
            }

            $bb = 0;
            foreach ($lowerGroup as $attempt) {
                $answers = $attempt->answers ?? [];
                $ans = $this->getStudentAnswer($answers, $question);
                if ($this->isAnswerCorrect($ans, $question)) {
                    $bb++;
                }
            }

            $pa = $ja > 0 ? $ba / $ja : 0;
            $pb = $jb > 0 ? $bb / $jb : 0;
            $d = $pa - $pb;

            if ($d < 0.2) {
                $text = 'Jelek';
            } elseif ($d <= 0.4) {
                $text = 'Cukup';
            } else {
                $text = 'Baik';
            }

            $result->put($question->id, [
                'question_id' => $question->id,
                'discrimination_index' => round($d, 2),
                'discrimination_text' => $text,
            ]);
        }

        return $result;
    }

    /**
     * Analisis efektifitas pengecoh per soal.
     * Setiap opsi salah harus dipilih oleh minimal 5% peserta.
     */
    public function analyzeDistractors(int $examId): Collection
    {
        $exam = Exam::with('questions')->findOrFail($examId);
        $attempts = $this->getValidAttempts($examId);
        $totalParticipants = $attempts->count();

        $result = collect();

        foreach ($exam->questions as $question) {
            $options = $question->options ?? [];
            $correct = strtoupper(trim($question->correct_answer));

            $counts = [];
            foreach ($options as $key => $val) {
                $counts[$key] = 0;
            }
            $counts['unanswered'] = 0;

            foreach ($attempts as $attempt) {
                $answers = $attempt->answers ?? [];
                $ans = $this->getStudentAnswer($answers, $question);

                if (is_null($ans) || $ans === '') {
                    $counts['unanswered']++;
                } else {
                    $ansUpper = strtoupper(trim($ans));
                    if (isset($counts[$ansUpper])) {
                        $counts[$ansUpper]++;
                    } else {
                        // Fallback or dynamic option key matching
                        $counts[$ansUpper] = 1;
                    }
                }
            }

            $distractorsAnalysis = [];
            foreach ($options as $key => $val) {
                $optionKey = strtoupper(trim($key));
                $isCorrectOption = ($optionKey === $correct);
                $chosenCount = $counts[$optionKey] ?? 0;
                $percentage = $totalParticipants > 0 ? ($chosenCount / $totalParticipants) : 0;

                // For incorrect options (distractors), check if selected by >= 5% of students
                $isEffective = false;
                if (! $isCorrectOption) {
                    $isEffective = ($percentage >= 0.05);
                }

                $distractorsAnalysis[$optionKey] = [
                    'option' => $optionKey,
                    'text' => $val,
                    'is_correct' => $isCorrectOption,
                    'count' => $chosenCount,
                    'percentage' => round($percentage * 100, 1),
                    'is_effective' => $isCorrectOption ? null : $isEffective,
                ];
            }

            $result->put($question->id, $distractorsAnalysis);
        }

        return $result;
    }

    /**
     * Hitung reliabilitas ujian menggunakan KR-20 (Kuder-Richardson).
     * r = [k / (k - 1)] * [1 - (sum(p * q) / s^2)]
     */
    public function calculateReliability(int $examId): float
    {
        $exam = Exam::with('questions')->findOrFail($examId);
        $attempts = $this->getValidAttempts($examId);
        $n = $attempts->count();
        $k = $exam->questions->count();

        if ($k <= 1 || $n <= 1) {
            return 0.0;
        }

        // Calculate p and q for each question
        $sumPq = 0.0;
        $difficultyMap = $this->calculateDifficultyIndex($examId);

        foreach ($exam->questions as $question) {
            $p = $difficultyMap->get($question->id)['difficulty_index'] ?? 0.0;
            $q = 1.0 - $p;
            $sumPq += ($p * $q);
        }

        // Calculate variance of correct counts of participants
        $correctCounts = $attempts->pluck('correct_count')->toArray();
        $mean = count($correctCounts) > 0 ? array_sum($correctCounts) / count($correctCounts) : 0;

        $sumSqDiff = 0.0;
        foreach ($correctCounts as $val) {
            $sumSqDiff += pow($val - $mean, 2);
        }

        // Use population variance
        $variance = $sumSqDiff / $n;

        if ($variance == 0.0) {
            return 0.0;
        }

        $r = ($k / ($k - 1)) * (1.0 - ($sumPq / $variance));

        // Reliability cannot exceed 1.0, and negative values are set to 0.0
        return max(0.0, min(1.0, round($r, 2)));
    }

    /**
     * Rangkuman lengkap analisis per ujian.
     */
    public function generateRecommendations(int $examId): Collection
    {
        $exam = Exam::with('questions')->findOrFail($examId);
        $difficultyMap = $this->calculateDifficultyIndex($examId);
        $discriminationMap = $this->calculateDiscriminationIndex($examId);
        $distractorMap = $this->analyzeDistractors($examId);

        $result = collect();

        foreach ($exam->questions as $question) {
            $pData = $difficultyMap->get($question->id);
            $dData = $discriminationMap->get($question->id);
            $distractors = $distractorMap->get($question->id) ?? [];

            $p = $pData['difficulty_index'] ?? 0.0;
            $d = $dData['discrimination_index'] ?? 0.0;

            // Check if there are ineffective distractors
            $ineffectiveCount = 0;
            foreach ($distractors as $dist) {
                if ($dist['is_effective'] === false) {
                    $ineffectiveCount++;
                }
            }

            if ($d < 0.2) {
                $recommendation = 'Buang';
                $notes = 'Daya pembeda jelek (D < 0.2). Soal tidak dapat membedakan siswa pandai dan kurang pandai.';
            } elseif ($d <= 0.4 || $p < 0.2 || $p > 0.8 || $ineffectiveCount > 0) {
                $recommendation = 'Revisi';
                $reasons = [];
                if ($d <= 0.4) {
                    $reasons[] = 'daya pembeda cukup';
                }
                if ($p < 0.2) {
                    $reasons[] = 'soal terlalu sulit';
                }
                if ($p > 0.8) {
                    $reasons[] = 'soal terlalu mudah';
                }
                if ($ineffectiveCount > 0) {
                    $reasons[] = "ada {$ineffectiveCount} opsi pengecoh tidak berfungsi efektif (< 5% pemilih)";
                }
                $notes = 'Soal perlu direvisi karena '.implode(', ', $reasons).'.';
            } else {
                $recommendation = 'Pertahankan';
                $notes = 'Soal berkualitas baik (daya pembeda baik dan tingkat kesukaran sedang).';
            }

            $result->put($question->id, [
                'question_id' => $question->id,
                'recommendation' => $recommendation,
                'notes' => $notes,
            ]);
        }

        return $result;
    }

    /**
     * Rangkuman lengkap analisis per ujian.
     */
    public function getFullAnalysis(int $examId): array
    {
        $exam = Exam::with(['subject', 'classroom'])->findOrFail($examId);
        $attempts = $this->getValidAttempts($examId);

        $scores = $attempts->pluck('score')->filter(fn ($s) => ! is_null($s))->values()->toArray();
        $totalParticipants = count($scores);

        // General stats
        $average = 0.0;
        $median = 0.0;
        $stdDev = 0.0;
        $highest = 0.0;
        $lowest = 0.0;

        if ($totalParticipants > 0) {
            $average = array_sum($scores) / $totalParticipants;
            $highest = max($scores);
            $lowest = min($scores);

            // Median
            sort($scores);
            $middle = (int) ($totalParticipants / 2);
            if ($totalParticipants % 2 === 0) {
                $median = ($scores[$middle - 1] + $scores[$middle]) / 2;
            } else {
                $median = $scores[$middle];
            }

            // Standard Deviation
            $sumSqDiff = 0.0;
            foreach ($scores as $s) {
                $sumSqDiff += pow($s - $average, 2);
            }
            $stdDev = sqrt($sumSqDiff / $totalParticipants);
        }

        $reliability = $this->calculateReliability($examId);
        $reliabilityText = 'Rendah';
        if ($reliability >= 0.7) {
            $reliabilityText = 'Tinggi';
        } elseif ($reliability >= 0.5) {
            $reliabilityText = 'Sedang';
        }

        $difficultyMap = $this->calculateDifficultyIndex($examId);
        $discriminationMap = $this->calculateDiscriminationIndex($examId);
        $recommendationMap = $this->generateRecommendations($examId);
        $distractorMap = $this->analyzeDistractors($examId);

        $questionsAnalysis = [];
        $difficultyCounts = ['Sulit' => 0, 'Sedang' => 0, 'Mudah' => 0];
        $recommendationCounts = ['Pertahankan' => 0, 'Revisi' => 0, 'Buang' => 0];

        foreach ($exam->questions as $question) {
            $diff = $difficultyMap->get($question->id);
            $disc = $discriminationMap->get($question->id);
            $rec = $recommendationMap->get($question->id);
            $dist = $distractorMap->get($question->id);

            $diffText = $diff['difficulty_text'] ?? 'Sedang';
            $recText = $rec['recommendation'] ?? 'Pertahankan';

            $difficultyCounts[$diffText]++;
            $recommendationCounts[$recText]++;

            $questionsAnalysis[] = [
                'question' => $question,
                'difficulty' => $diff,
                'discrimination' => $disc,
                'recommendation' => $rec,
                'distractors' => $dist,
            ];
        }

        return [
            'exam' => $exam,
            'stats' => [
                'total_participants' => $totalParticipants,
                'average' => round($average, 2),
                'median' => round($median, 2),
                'std_dev' => round($stdDev, 2),
                'highest' => round($highest, 2),
                'lowest' => round($lowest, 2),
                'reliability' => $reliability,
                'reliability_text' => $reliabilityText,
            ],
            'difficulty_counts' => $difficultyCounts,
            'recommendation_counts' => $recommendationCounts,
            'questions' => $questionsAnalysis,
        ];
    }
}
