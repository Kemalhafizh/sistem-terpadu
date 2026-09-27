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
        if (! empty($attempt->question_order)) {
            return $attempt->question_order;
        }

        $shuffledIds = $this->computeQuestionOrder($exam, $student->id);

        $attempt->update(['question_order' => $shuffledIds]);

        return $shuffledIds;
    }

    /**
     * Deterministic question order for a student (seeded by student + exam),
     * or authored pivot order when shuffle_questions is off. Pure — never persists.
     */
    public function computeQuestionOrder(Exam $exam, int $studentId): array
    {
        if ($exam->shuffle_questions) {
            $questionIds = $exam->questions->pluck('id')->toArray();
            $seed = $studentId * 7919 + $exam->id * 104729;

            return $this->deterministicShuffle($questionIds, $seed);
        }

        return $this->authoredQuestionIds($exam);
    }

    /**
     * Question ids in authored (pivot `order`) sequence.
     */
    private function authoredQuestionIds(Exam $exam): array
    {
        return $exam->questions->sortBy(function ($q) {
            return $q->pivot->order ?? 0;
        })->pluck('id')->values()->toArray();
    }

    /**
     * Get deterministically shuffled options for a question based on a student seed.
     */
    public function getShuffledOptions(QuestionBank $question, int $studentId, int $examId): array
    {
        $options = $question->options;
        if (empty($options) || ! is_array($options) || $question->type !== 'multiple_choice') {
            return $options ?? [];
        }

        $seed = $studentId * 7919 + $examId * 104729 + $question->id;

        return $this->deterministicShuffleAssociative($options, $seed);
    }

    /**
     * Options in the order a student should see them: shuffled (original keys
     * kept, only display order changes — same as the web take view) when the
     * exam has shuffle_options on, otherwise authored order.
     */
    public function getDisplayOptions(Exam $exam, QuestionBank $question, int $studentId): array
    {
        if ($exam->shuffle_options && $question->type === 'multiple_choice') {
            return $this->getShuffledOptions($question, $studentId, $exam->id);
        }

        return is_array($question->options) ? $question->options : [];
    }

    /**
     * Ordered, per-student question set for the mobile API and the encrypted
     * vault payload, so both agree with each other and with the web path.
     * Reuses the attempt's stored question_order when there is an attempt
     * (initializing it if empty); without one, computes the same seeded order
     * without persisting it.
     *
     * @return array<int, array{question: QuestionBank, number: int, options: array}>
     */
    public function buildStudentQuestionSet(Exam $exam, Student $student, ?ExamAttempt $attempt = null): array
    {
        $orderedIds = $attempt
            ? $this->getOrInitializeQuestionOrder($exam, $student, $attempt)
            : $this->computeQuestionOrder($exam, $student->id);

        $questions = $exam->questions->keyBy('id');

        // Questions attached after the order was stored are appended in authored order.
        $orderedIds = array_merge($orderedIds, array_values(array_diff($this->authoredQuestionIds($exam), $orderedIds)));

        $set = [];
        $number = 1;
        foreach ($orderedIds as $qId) {
            $question = $questions->get($qId);
            if (! $question) {
                continue;
            }
            $set[] = [
                'question' => $question,
                'number' => $number++,
                'options' => $this->formatOptions($this->getDisplayOptions($exam, $question, $student->id)),
            ];
        }

        return $set;
    }

    /**
     * Normalize both stored option shapes — ['A' => 'text'] and
     * [['key' => 'A', 'text' => ..., 'image_url' => ...]] — into a list of
     * ['key', 'text', 'image_url'], preserving the given order.
     */
    public function formatOptions(array $options): array
    {
        $formatted = [];
        foreach ($options as $key => $opt) {
            if (is_array($opt)) {
                $formatted[] = [
                    'key' => (string) ($opt['key'] ?? $key),
                    'text' => (string) ($opt['text'] ?? ''),
                    'image_url' => $opt['image_url'] ?? $opt['image'] ?? null,
                ];
            } else {
                $formatted[] = [
                    'key' => (string) $key,
                    'text' => (string) $opt,
                    'image_url' => null,
                ];
            }
        }

        return $formatted;
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
