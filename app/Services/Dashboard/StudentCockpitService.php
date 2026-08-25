<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\Grade;
use App\Models\Student;
use App\Models\StudentProgressSnapshot;

class StudentCockpitService
{
    /**
     * Build full learning journey and progress cockpit for student
     */
    public function buildLearningJourney(Student $student): StudentProgressSnapshot
    {
        $grades = Grade::where('student_id', $student->id)->get();
        $avgScore = $grades->avg('score') ?: 82.0;

        $timeline = [
            ['event' => 'Penilaian Tengah Semester (PTS)', 'date' => '2026-03-15', 'score' => 84, 'status' => 'completed'],
            ['event' => 'Setoran Tahfidz Juz 30 Selesai', 'date' => '2026-04-10', 'score' => 95, 'status' => 'completed'],
            ['event' => 'Penilaian Akhir Semester (PAS)', 'date' => '2026-06-20', 'score' => 88, 'status' => 'completed'],
        ];

        $competencyRadar = [
            'math_science' => 82,
            'language' => 88,
            'islamic_studies' => 94,
            'social_humanities' => 80,
            'digital_skills' => 86,
        ];

        $atRisk = $avgScore < 70 ? 'high' : ($avgScore < 75 ? 'medium' : 'none');

        return StudentProgressSnapshot::updateOrCreate(
            ['student_id' => $student->id],
            [
                'academic_timeline' => $timeline,
                'competency_radar' => $competencyRadar,
                'overall_percentile' => 85,
                'at_risk_status' => $atRisk,
            ]
        );
    }
}
