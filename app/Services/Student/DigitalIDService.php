<?php

namespace App\Services\Student;

use App\Models\DigitalIdCard;
use App\Models\DigitalIdScan;
use Illuminate\Support\Str;

class DigitalIDService
{
    public function generateCard(int $studentId): DigitalIdCard
    {
        return DigitalIdCard::create([
            'student_id' => $studentId,
            'card_hash' => Str::random(32),
            'issue_date' => now(),
            'expiry_date' => now()->addYears(3),
            'is_active' => true,
        ]);
    }

    public function recordScan(int $cardId, string $location = 'Gerbang Utama', string $scanType = 'tap_in'): DigitalIdScan
    {
        return DigitalIdScan::create([
            'card_id' => $cardId,
            'location' => $location,
            'scan_type' => $scanType,
            'scanned_at' => now(),
        ]);
    }
}
