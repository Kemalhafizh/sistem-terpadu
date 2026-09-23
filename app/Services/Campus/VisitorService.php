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

    /**
     * Scan a visitor's real badge_number (pre-registered at the front desk)
     * to toggle them in or out: pre_registered -> checked in, active ->
     * checked out. Throws when the badge doesn't exist or is already
     * completed, so the scanner can surface an honest error instead of
     * guessing.
     */
    public function scanBadge(string $badgeNumber): VisitorLog
    {
        $visitor = VisitorLog::where('badge_number', $badgeNumber)->first();

        if (! $visitor) {
            throw new \Exception('Kode badge tamu tidak ditemukan. Pastikan tamu sudah melakukan pra-registrasi.');
        }

        if ($visitor->status === 'completed') {
            throw new \Exception('Kunjungan tamu ini sudah selesai (check-out).');
        }

        return $visitor->status === 'active'
            ? $this->processCheckOut($visitor)
            : $this->processCheckIn($visitor);
    }
}
