<?php

namespace App\Services\Microlearning;

use App\Models\SpacedRepetitionLog;
use Carbon\Carbon;

class SpacedRepetitionService
{
    public function calculateNextReview($quality, $easinessFactor, $repetitionCount, $intervalDays) {}

    public function logReviewAttempt($studentId, $memoryCardId, $quality) {}

    public function getDueCards($studentId)
    {
        return SpacedRepetitionLog::where('student_id', $studentId)
            ->where('next_review_date', '<=', Carbon::today())->get();
    }

    public function analyzeRetentionRate($studentId)
    {
        return [];
    }
}
