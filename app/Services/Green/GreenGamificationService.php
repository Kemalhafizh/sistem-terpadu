<?php

namespace App\Services\Green;

use App\Models\EcoParticipation;

class GreenGamificationService
{
    public function awardEcoPoints()
    {
        return true;
    }

    public function validateEvidence()
    {
        return true;
    }

    public function updateLeaderboard()
    {
        return [];
    }
}
