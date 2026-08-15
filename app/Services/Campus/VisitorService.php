<?php

namespace App\Services\Campus;

use App\Models\VisitorLog;
use Illuminate\Support\Str;

class VisitorService
{
    public function preRegister(array $data)
    {
        $data['badge_number'] = strtoupper(Str::random(8));
        $data['status'] = 'pre_registered';
        return VisitorLog::create($data);
    }

    public function processCheckIn(VisitorLog $visitor)
    {
        $visitor->update([
            'status' => 'active',
            'check_in_time' => now(),
        ]);
        return $visitor;
    }

    public function processCheckOut(VisitorLog $visitor)
    {
        $visitor->update([
            'status' => 'completed',
            'check_out_time' => now(),
        ]);
        return $visitor;
    }

    public function getActiveVisitors()
    {
        return VisitorLog::where('status', 'active')->get();
    }
}
