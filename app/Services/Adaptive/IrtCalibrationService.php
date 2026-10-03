<?php

namespace App\Services\Adaptive;

use App\Models\ExamAttempt;
use App\Models\IrtItemParameter;
use App\Models\IrtStudentAbility;
use App\Models\QuestionBank;
use App\Services\CbtService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Rasch (1PL) calibration from real CBT answers.
 *
 * Every parameter used to be rand(): item difficulty/discrimination/guessing,
 * student theta, and a batch run that always reported 10 calibrated items.
 * Now everything is computed from finished exam attempts. Under the Rasch
 * model discrimination is fixed at 1 and guessing at 0 (the same model
 * calculateProbability() already used). Without responses there is nothing
 * to estimate, so the result is null and nothing is written.
 */
class IrtCalibrationService
{
    /** Attempts whose answers are final. */
    public const FINISHED_STATUSES = ['submitted', 'graded', 'blocked_violation', 'timeout', 'force_closed'];

    /**
     * Correctness of every answered response to a bank question, keyed by student id.
     * Unanswered (skipped / not reached) items are left out.
     *
     * @return Collection<int, bool>
     */
    public function itemResponses(QuestionBank $question): Collection
    {
        $examIds = DB::table('exam_questions')->where('question_bank_id', $question->id)->pluck('exam_id');

        return ExamAttempt::whereIn('exam_id', $examIds)
            ->whereIn('status', self::FINISHED_STATUSES)
            ->get(['student_id', 'answers'])
            ->mapWithKeys(fn (ExamAttempt $attempt) => [$attempt->student_id => ($attempt->answers ?? [])[$question->id] ?? null])
            ->reject(fn ($answer) => $answer === null || $answer === '')
            ->map(fn ($answer) => CbtService::isCorrectAnswer($question, $answer));
    }

    public function calibrateItem(int $questionId): ?IrtItemParameter
    {
        $question = QuestionBank::withTrashed()->findOrFail($questionId);
        $responses = $this->itemResponses($question);
        $n = $responses->count();

        if ($n === 0) {
            return null;
        }

        $correct = $responses->filter()->count();
        // Continuity correction keeps the logit finite when everyone (or no one) is right.
        $p = ($correct + 0.5) / ($n + 1);

        return IrtItemParameter::updateOrCreate(
            ['question_id' => $questionId],
            [
                'difficulty' => round(log((1 - $p) / $p), 4),
                'discrimination' => 1,
                'guessing' => 0,
                'standard_error' => round(1 / sqrt($n * $p * (1 - $p)), 4),
                'total_attempts' => $n,
                'correct_count' => $correct,
                'p_value' => round($correct / $n, 4),
                'last_calibrated_at' => now(),
            ]
        );
    }

    /**
     * Rasch ability (theta) of a student in a subject, PROX approximation:
     * logit of the student's corrected success rate plus the mean difficulty
     * of the items answered. Returns null when the student answered nothing.
     */
    public function estimateStudentAbility(int $studentId, int $subjectId): ?float
    {
        $attempts = ExamAttempt::where('student_id', $studentId)
            ->whereIn('status', self::FINISHED_STATUSES)
            ->whereHas('exam', fn ($q) => $q->where('subject_id', $subjectId))
            ->with('exam.questions')
            ->get();

        $responses = collect();
        foreach ($attempts as $attempt) {
            foreach ($attempt->exam->questions as $question) {
                $answer = ($attempt->answers ?? [])[$question->id] ?? null;
                if ($answer !== null && $answer !== '') {
                    $responses->push(['question_id' => $question->id, 'correct' => CbtService::isCorrectAnswer($question, $answer)]);
                }
            }
        }

        if ($responses->isEmpty()) {
            return null;
        }

        $difficulties = $responses->pluck('question_id')->unique()
            ->mapWithKeys(fn (int $id) => [$id => (float) ($this->calibrateItem($id)?->difficulty ?? 0.0)]);

        $n = $responses->count();
        $p = ($responses->where('correct', true)->count() + 0.5) / ($n + 1);
        $theta = log($p / (1 - $p)) + $responses->avg(fn ($r) => $difficulties[$r['question_id']]);

        $information = $responses->sum(function ($r) use ($theta, $difficulties) {
            $prob = $this->calculateProbability($theta, $difficulties[$r['question_id']]);

            return $prob * (1 - $prob);
        });

        IrtStudentAbility::updateOrCreate(
            ['student_id' => $studentId, 'subject_id' => $subjectId],
            [
                'theta' => round($theta, 4),
                'standard_error' => $information > 0 ? round(1 / sqrt($information), 4) : null,
                'items_attempted' => $n,
                'last_estimated_at' => now(),
            ]
        );

        return round($theta, 4);
    }

    public function calculateProbability(float $theta, float $difficulty): float
    {
        // Rasch model: 1/(1+exp(-(theta-b)))
        return 1 / (1 + exp(-($theta - $difficulty)));
    }

    public function getItemCharacteristicCurve(int $questionId): array
    {
        $item = IrtItemParameter::where('question_id', $questionId)->first();
        if (! $item) {
            return [];
        }

        $curve = [];
        for ($theta = -3.0; $theta <= 3.0; $theta += 0.5) {
            $curve[] = [
                'theta' => $theta,
                'probability' => $this->calculateProbability($theta, (float) $item->difficulty),
            ];
        }

        return $curve;
    }

    /**
     * Calibrate every question of an exam that has responses.
     *
     * @return array{status: string, calibrated_count: int, skipped_count: int}
     */
    public function batchCalibrateExam(int $examId): array
    {
        $questionIds = DB::table('exam_questions')->where('exam_id', $examId)->pluck('question_bank_id');

        $calibrated = $questionIds->filter(fn ($id) => $this->calibrateItem((int) $id) !== null)->count();

        return [
            'status' => $calibrated > 0 ? 'success' : 'no_data',
            'calibrated_count' => $calibrated,
            'skipped_count' => $questionIds->count() - $calibrated,
        ];
    }
}
