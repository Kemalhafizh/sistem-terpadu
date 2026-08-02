<?php

namespace App\Services\Community;

use App\Models\MentorProfile;
use App\Models\MentorshipPair;

class MentorshipService
{
    public function registerMentor(int $alumniId, array $expertise = [], ?string $bio = null): MentorProfile
    {
        return MentorProfile::create([
            'alumni_id' => $alumniId,
            'expertise_areas' => $expertise,
            'max_mentees' => 3,
            'is_available' => true,
            'bio' => $bio,
        ]);
    }

    public function createPair(int $mentorProfileId, int $studentId, ?string $goals = null): MentorshipPair
    {
        return MentorshipPair::create([
            'mentor_profile_id' => $mentorProfileId,
            'student_id' => $studentId,
            'goals' => $goals,
            'status' => 'active',
            'started_at' => now(),
        ]);
    }
}
