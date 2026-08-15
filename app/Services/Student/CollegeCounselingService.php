<?php

namespace App\Services\Student;

use App\Models\CollegeApplication;

class CollegeCounselingService
{
    public function calculateMatchProbability($studentId, $catalogId)
    {
        return 85.5;
    }

    public function trackApplication($applicationId)
    {
        return CollegeApplication::with('catalog')->find($applicationId);
    }
}
