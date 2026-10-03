<?php

namespace App\Services\Adaptive;

use App\Models\CatItemResponse;
use App\Models\CatSession;
use App\Models\IrtItemParameter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatEngineService
{
    public function startSession(int $studentId, int $examId): CatSession
    {
        return CatSession::create([
            'uuid' => Str::uuid(),
            'student_id' => $studentId,
            'exam_id' => $examId,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);
    }

    /**
     * Next question for the session: one of the exam's questions not yet
     * administered, with calibrated difficulty closest to the current theta
     * (maximum information under the Rasch model). Uncalibrated questions
     * follow, in exam order. Null when the pool is used up.
     *
     * (This used to return rand(1, 100): an arbitrary id that might not even
     * be a question, let alone one of this exam's.)
     */
    public function selectNextItem(CatSession $session): ?int
    {
        $administered = $session->responses()->pluck('question_id');

        $pool = DB::table('exam_questions')
            ->where('exam_id', $session->exam_id)
            ->whereNotIn('question_bank_id', $administered)
            ->orderBy('order')->orderBy('id')
            ->pluck('question_bank_id');

        if ($pool->isEmpty()) {
            return null;
        }

        $difficulties = IrtItemParameter::whereIn('question_id', $pool)->pluck('difficulty', 'question_id');
        $theta = (float) $session->current_theta;

        $calibrated = $pool->filter(fn ($id) => $difficulties->has($id))
            ->sortBy(fn ($id) => abs((float) $difficulties[$id] - $theta));

        return (int) ($calibrated->first() ?? $pool->first());
    }

    public function processResponse(CatSession $session, int $questionId, bool $isCorrect): float
    {
        $thetaBefore = (float) $session->current_theta;

        // Update theta based on correctness
        $adjustment = $isCorrect ? 0.2 : -0.2;
        $session->current_theta += $adjustment;
        $session->items_administered += 1;
        if ($isCorrect) {
            $session->correct_count += 1;
        }
        $session->save();

        // Record the response so selectNextItem() never repeats a question.
        // An uncalibrated item sits at the scale's origin (difficulty 0).
        CatItemResponse::create([
            'cat_session_id' => $session->id,
            'question_id' => $questionId,
            'sequence_number' => $session->items_administered,
            'is_correct' => $isCorrect,
            'theta_before' => $thetaBefore,
            'theta_after' => $session->current_theta,
            'item_difficulty' => (float) (IrtItemParameter::where('question_id', $questionId)->value('difficulty') ?? 0),
        ]);

        return $session->current_theta;
    }

    public function checkTermination(CatSession $session): bool
    {
        if ($session->items_administered >= $session->max_items) {
            $session->status = 'completed';
            $session->completed_at = now();
            $session->termination_rule = 'max_items';
            $session->save();

            return true;
        }

        return false;
    }

    public function getFinalScore(CatSession $session): array
    {
        return [
            'theta' => $session->current_theta,
            'items_administered' => $session->items_administered,
            'correct_count' => $session->correct_count,
        ];
    }
}
