<?php

namespace App\Services\Knowledge;

use App\Models\LessonStudy;
use App\Models\LessonStudyObserver;
use Illuminate\Support\Str;

class LessonStudyService
{
    public function createSession(array $data): LessonStudy
    {
        $data['uuid'] = Str::uuid();

        return LessonStudy::create($data);
    }

    public function advancePhase(LessonStudy $study): void
    {
        $phases = ['plan', 'do', 'see', 'reflect', 'completed'];
        $currentIndex = array_search($study->phase, $phases);
        if ($currentIndex !== false && $currentIndex < count($phases) - 1) {
            $study->update(['phase' => $phases[$currentIndex + 1]]);
        }
    }

    public function submitObservation(int $studyId, int $observerId, array $data): void
    {
        $observer = LessonStudyObserver::where('lesson_study_id', $studyId)->where('observer_id', $observerId)->first();
        if ($observer) {
            $observer->update($data);
        }
    }

    public function calculateAverageScores(int $studyId): array
    {
        $observers = LessonStudyObserver::where('lesson_study_id', $studyId)->get();
        if ($observers->isEmpty()) {
            return [];
        }

        return [
            'student_engagement_score' => $observers->avg('student_engagement_score'),
            'teaching_quality_score' => $observers->avg('teaching_quality_score'),
            'material_clarity_score' => $observers->avg('material_clarity_score'),
        ];
    }
}
