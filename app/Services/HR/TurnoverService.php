<?php

namespace App\Services\HR;

use App\Models\ExitInterview;
use App\Models\TurnoverAnalytic;

class TurnoverService
{
    public function scheduleExitInterview(array $data)
    {
        return ExitInterview::create($data);
    }

    public function calculateTurnoverRate($department, $year, $month)
    {
        // Implementation
    }

    public function generateRetentionReport()
    {
        // Implementation
    }
}