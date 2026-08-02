<?php

namespace App\Services\Community;

use App\Models\VolunteerOpportunity;
use App\Models\VolunteerLog;

class VolunteerService
{
    public function createOpportunity(string $title, int $hours, int $organizerId, string $date): VolunteerOpportunity
    {
        return VolunteerOpportunity::create([
            'title' => $title,
            'required_volunteers' => 5,
            'required_hours' => $hours,
            'event_date' => $date,
            'organizer_id' => $organizerId,
            'status' => 'open',
        ]);
    }

    public function logHours(int $opportunityId, int $userId, int $hours): VolunteerLog
    {
        return VolunteerLog::create([
            'opportunity_id' => $opportunityId,
            'user_id' => $userId,
            'hours_logged' => $hours,
            'status' => 'pending',
        ]);
    }
}
