<?php

namespace App\Services\HumanCapital;

use App\Models\CpdActivity;
use App\Models\CpdTarget;

class CpdTrackerService
{
    public function logActivity(array $data, int $employeeId): CpdActivity
    {
        return CpdActivity::create([
            'employee_id' => $employeeId,
            'cpd_category_id' => $data['cpd_category_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'provider' => $data['provider'] ?? 'Kemendikbudristek / YBWSA',
            'start_date' => $data['start_date'] ?? now()->subDays(2),
            'end_date' => $data['end_date'] ?? now(),
            'hours' => $data['hours'] ?? 16,
            'credits_earned' => $data['credits_earned'] ?? 4.0,
            'status' => 'pending',
        ]);
    }

    public function verifyActivity(CpdActivity $activity, int $verifierId, bool $approve = true): CpdActivity
    {
        $activity->update([
            'status' => $approve ? 'verified' : 'rejected',
            'verified_by' => $verifierId,
            'verified_at' => now(),
        ]);

        if ($approve) {
            $target = CpdTarget::firstOrCreate(
                ['employee_id' => $activity->employee_id, 'academic_year' => '2026/2027'],
                ['target_credits' => 12.0, 'achieved_credits' => 0]
            );

            $target->increment('achieved_credits', $activity->credits_earned);
            if ($target->achieved_credits >= $target->target_credits) {
                $target->update(['status' => 'exceeded']);
            }
        }

        return $activity;
    }
}
