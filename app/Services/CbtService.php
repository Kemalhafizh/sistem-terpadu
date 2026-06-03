<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\QuestionBank;
use App\Models\Student;

class CbtService
{
    /**
     * Get or initialize the deterministic question order for a student attempt.
     */
    public function getOrInitializeQuestionOrder(Exam $exam, Student $student, ExamAttempt $attempt): array
    {
        if (!empty($attempt->question_order)) {
            return $attempt->question_order;
        }

        $questions = $exam->questions;
        
        if ($exam->shuffle_questions) {
            $questionIds = $questions->pluck('id')->toArray();
            $seed = $student->id * 7919 + $exam->id * 104729;
            $shuffledIds = $this->deterministicShuffle($questionIds, $seed);
        } else {
            // Sort by pivot order or id
            $shuffledIds = $questions->sortBy(function ($q) {
                return $q->pivot->order ?? 0;
            })->pluck('id')->toArray();
        }

        $attempt->update(['question_order' => $shuffledIds]);
        return $shuffledIds;
    }

    /**
     * Get deterministically shuffled options for a question based on a student seed.
     */
    public function getShuffledOptions(QuestionBank $question, int $studentId, int $examId): array
    {
        $options = $question->options;
        if (empty($options) || !is_array($options) || $question->type !== 'multiple_choice') {
            return $options ?? [];
        }

        $seed = $studentId * 7919 + $examId * 104729 + $question->id;
        return $this->deterministicShuffleAssociative($options, $seed);
    }

    /**
     * Auto-grades an exam attempt based on correct answers and question weights.
     */
    public function autoGrade(ExamAttempt $attempt): ExamAttempt
    {
        $exam = $attempt->exam;
        $questions = $exam->questions;
        $answers = $attempt->answers ?? [];

        $totalWeight = 0;
        $weightedCorrectScore = 0;
        $correctCount = 0;
        $wrongCount = 0;
        $unansweredCount = 0;

        foreach ($questions as $question) {
            $weight = $question->pivot->weight ?? 1.00;
            $totalWeight += $weight;

            $studentAnswer = $answers[$question->id] ?? null;

            if (is_null($studentAnswer) || $studentAnswer === '') {
                $unansweredCount++;
            } else {
                $isCorrect = false;

                if ($question->type === 'multiple_choice') {
                    $isCorrect = (strtoupper(trim($studentAnswer)) === strtoupper(trim($question->correct_answer)));
                } elseif ($question->type === 'true_false') {
                    $isCorrect = (strtolower(trim($studentAnswer)) === strtolower(trim($question->correct_answer)));
                } else {
                    // Essay: Simple case-insensitive exact trim match for automated lifecycle testing
                    $isCorrect = (strtolower(trim($studentAnswer)) === strtolower(trim($question->correct_answer)));
                }

                if ($isCorrect) {
                    $correctCount++;
                    $weightedCorrectScore += $weight;
                } else {
                    $wrongCount++;
                }
            }
        }

        $score = $totalWeight > 0 ? ($weightedCorrectScore / $totalWeight) * 100 : 0;

        // Maintain 'blocked_violation' status if already set, otherwise set as 'graded'
        $newStatus = $attempt->status === 'blocked_violation' ? 'blocked_violation' : 'graded';

        $attempt->update([
            'score' => round($score, 2),
            'correct_count' => $correctCount,
            'wrong_count' => $wrongCount,
            'unanswered_count' => $unansweredCount,
            'status' => $newStatus,
            'submitted_at' => $attempt->submitted_at ?? now(),
        ]);

        return $attempt;
    }

    /**
     * Fisher-Yates deterministic shuffle for simple arrays.
     */
    private function deterministicShuffle(array $array, int $seed): array
    {
        mt_srand($seed);
        $shuffled = array_values($array);
        $n = count($shuffled);
        
        for ($i = $n - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            $temp = $shuffled[$i];
            $shuffled[$i] = $shuffled[$j];
            $shuffled[$j] = $temp;
        }

        return $shuffled;
    }

    /**
     * Fisher-Yates deterministic shuffle for associative arrays, maintaining original keys but changing display order.
     */
    private function deterministicShuffleAssociative(array $array, int $seed): array
    {
        mt_srand($seed);
        $keys = array_keys($array);
        $n = count($keys);
        
        for ($i = $n - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            $temp = $keys[$i];
            $keys[$i] = $keys[$j];
            $keys[$j] = $temp;
        }

        $shuffled = [];
        foreach ($keys as $key) {
            $shuffled[$key] = $array[$key];
        }

        return $shuffled;
    }
}
