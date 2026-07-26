<?php

namespace App\Services\HumanCapital;

use App\Models\LeadershipPosition;
use App\Models\SuccessionCandidate;

class SuccessionPlanningService
{
    public function identifyPosition(string $title, ?int $currentHolderId = null): LeadershipPosition
    {
        return LeadershipPosition::firstOrCreate(
            ['title' => $title],
            [
                'current_holder_id' => $currentHolderId,
                'succession_priority' => 'high',
                'review_date' => now()->addYear(),
            ]
        );
    }

    public function addCandidate(LeadershipPosition $position, int $candidateId, string $readinessLevel, array $strengths, array $developmentAreas, ?int $endorserId = null): SuccessionCandidate
    {
        return SuccessionCandidate::updateOrCreate(
            [
                'leadership_position_id' => $position->id,
                'candidate_id' => $candidateId,
            ],
            [
                'readiness_level' => $readinessLevel,
                'strengths' => $strengths,
                'development_areas' => $developmentAreas,
                'development_plan' => 'Mengikuti Diklat Kepemimpinan Manajerial Sekolah & Mentoring Eksekutif',
                'endorsed_by' => $endorserId,
            ]
        );
    }
}
