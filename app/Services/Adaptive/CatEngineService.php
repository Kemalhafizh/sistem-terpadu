<?php

namespace App\Services\Adaptive;

use App\Models\CatSession;
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

    public function selectNextItem(CatSession $session): ?int
    {
        // Dummy logic to return a question ID
        // In real app, calculate maximum information function
        return rand(1, 100); 
    }

    public function processResponse(CatSession $session, int $questionId, bool $isCorrect): float
    {
        // Update theta based on correctness
        $adjustment = $isCorrect ? 0.2 : -0.2;
        $session->current_theta += $adjustment;
        $session->items_administered += 1;
        if ($isCorrect) {
            $session->correct_count += 1;
        }
        $session->save();

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
