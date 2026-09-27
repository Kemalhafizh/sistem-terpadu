<?php

namespace App\Services\HumanCapital;

use App\Models\TeacherWorkload;

class WorkloadBalancingService
{
    public function calculateWorkload(int $employeeId, int $teachingHours, array $additionalDuties = [], array $extracurricularDuties = []): TeacherWorkload
    {
        $additionalScore = count($additionalDuties) * 4.0;
        $extraScore = count($extracurricularDuties) * 2.0;

        $totalScore = $teachingHours + $additionalScore + $extraScore;

        $status = 'balanced';
        if ($totalScore < 24) {
            $status = 'under';
        } elseif ($totalScore > 36) {
            $status = 'over';
        }

        return TeacherWorkload::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'academic_year' => '2026/2027',
                'semester' => 'ganjil',
            ],
            [
                'teaching_hours_per_week' => $teachingHours,
                'additional_duties' => $additionalDuties,
                'extracurricular_duties' => $extracurricularDuties,
                'total_workload_score' => $totalScore,
                'status' => $status,
            ]
        );
    }
}
