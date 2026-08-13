<?php

namespace App\Services\Adaptive;

use App\Models\LearningPath;
use App\Models\LearningPathItem;

class LearningPathService
{
    public function diagnosWeaknesses(int $studentId, int $subjectId): array
    {
        // Dummy weaknesses
        return ['KD1', 'KD3', 'KD4'];
    }

    public function generatePath(int $studentId, int $subjectId): LearningPath
    {
        $weaknesses = $this->diagnosWeaknesses($studentId, $subjectId);

        $path = LearningPath::create([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'title' => 'Jalur Belajar Adaptif',
            'weak_competencies' => $weaknesses,
            'status' => 'generated',
        ]);

        return $path;
    }

    public function markItemCompleted(int $itemId): void
    {
        $item = LearningPathItem::find($itemId);
        if ($item) {
            $item->is_completed = true;
            $item->completed_at = now();
            $item->save();
        }
    }

    public function calculateProgress(int $pathId): int
    {
        $path = LearningPath::with('items')->find($pathId);
        if (!$path || $path->items->isEmpty()) return 0;

        $completed = $path->items->where('is_completed', true)->count();
        $total = $path->items->count();

        $percentage = (int) (($completed / $total) * 100);
        $path->progress_percentage = $percentage;
        $path->save();

        return $percentage;
    }

    public function evaluateImprovement(int $pathId): float
    {
        $path = LearningPath::find($pathId);
        if (!$path || is_null($path->pre_score) || is_null($path->post_score)) return 0.0;

        return $path->post_score - $path->pre_score;
    }
}
