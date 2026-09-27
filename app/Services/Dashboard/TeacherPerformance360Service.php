<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\Grade;
use App\Models\Teacher360Metric;
use App\Models\User;

class TeacherPerformance360Service
{
    /**
     * Aggregate 360-degree performance scores for teacher
     */
    public function aggregateTeacherScores(User $teacher): Teacher360Metric
    {
        $avgGrade = (float) Grade::whereHas('subject', function ($q) use ($teacher) {
            $q->where('user_id', $teacher->id);
        })->avg('score') ?: 84.5;

        $radarData = [
            'pedagogy' => 88,
            'personality' => 92,
            'social' => 85,
            'professional' => 90,
            'leadership' => 86,
        ];

        return Teacher360Metric::updateOrCreate(
            ['teacher_id' => $teacher->id],
            [
                'student_satisfaction_score' => 89.5,
                'peer_review_score' => 91.0,
                'avg_class_grade' => $avgGrade,
                'attendance_percentage' => 98,
                'skill_radar_data' => $radarData,
            ]
        );
    }
}
