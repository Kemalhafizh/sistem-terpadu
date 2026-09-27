<?php

namespace App\Services\HR;

use App\Models\WellnessLog;
use App\Models\WellnessProgram;

class WellnessService
{
    public function createProgram(array $data)
    {
        return WellnessProgram::create($data);
    }

    public function logParticipation(array $data)
    {
        return WellnessLog::create($data);
    }

    public function calculateWellnessScore($employeeId)
    {
        return WellnessLog::where('employee_id', $employeeId)
            ->join('wellness_programs', 'wellness_logs.wellness_program_id', '=', 'wellness_programs.id')
            ->sum('wellness_programs.points_reward');
    }
}
