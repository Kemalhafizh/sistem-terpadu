<?php

namespace App\Services\Academic;

use App\Models\AcademicPathway;
use App\Models\GraduationRequirement;
use App\Models\StudentGraduationProgress;
use App\Models\StudentPathwayEnrollment;

class AcademicPathwayService
{
    public function enrollStudent(int $studentId, int $pathwayId): StudentPathwayEnrollment
    {
        return StudentPathwayEnrollment::updateOrCreate(
            ['student_id' => $studentId],
            [
                'academic_pathway_id' => $pathwayId,
                'enrolled_at' => now(),
                'status' => 'active',
            ]
        );
    }

    public function checkGraduationReadiness(int $studentId): array
    {
        $requirements = GraduationRequirement::where('is_mandatory', true)->get();
        $progresses = StudentGraduationProgress::where('student_id', $studentId)->get();

        $checklist = [];
        $totalFulfilled = 0;

        foreach ($requirements as $req) {
            $prog = $progresses->where('graduation_requirement_id', $req->id)->first();
            $isFulfilled = $prog ? $prog->is_fulfilled : false;
            if ($isFulfilled) $totalFulfilled++;

            $checklist[] = [
                'requirement_id' => $req->id,
                'type' => $req->requirement_type,
                'description' => $req->description,
                'threshold' => $req->threshold_value,
                'current_value' => $prog ? $prog->current_value : '0',
                'is_fulfilled' => $isFulfilled,
            ];
        }

        $totalReqs = $requirements->count();
        $readinessScore = $totalReqs > 0 ? round(($totalFulfilled / $totalReqs) * 100, 2) : 100;

        return [
            'readiness_score' => $readinessScore,
            'is_ready_for_graduation' => $readinessScore === 100.0,
            'checklist' => $checklist,
        ];
    }
}
