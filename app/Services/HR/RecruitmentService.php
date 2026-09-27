<?php

namespace App\Services\HR;

use App\Models\JobApplicant;
use App\Models\JobVacancy;

class RecruitmentService
{
    public function publishVacancy(array $data)
    {
        return JobVacancy::create($data);
    }

    public function movePipelineStage(JobApplicant $applicant, $newStage)
    {
        $applicant->update(['pipeline_stage' => $newStage]);

        return $applicant;
    }

    public function scheduleInterview(JobApplicant $applicant, $date)
    {
        // Implementation
    }

    public function generateOfferLetter(JobApplicant $applicant)
    {
        // Implementation
    }
}
