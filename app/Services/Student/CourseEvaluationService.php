<?php

namespace App\Services\Student;

use App\Models\CourseEvaluationResponse;

class CourseEvaluationService
{
    public function submitEvaluation(int $evaluationId, int $studentId, int $rating, ?string $comments = null): CourseEvaluationResponse
    {
        return CourseEvaluationResponse::create([
            'evaluation_id' => $evaluationId,
            'student_id' => $studentId,
            'rating' => $rating,
            'comments' => $comments,
            'is_anonymous' => true,
        ]);
    }
}
