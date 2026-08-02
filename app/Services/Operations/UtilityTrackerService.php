<?php

namespace App\Services\Operations;

use App\Models\UtilityBill;
use App\Models\CarbonEmission;

class UtilityTrackerService
{
    public function recordBill(string $type, string $period, float $amount, float $usage = 0): UtilityBill
    {
        $bill = UtilityBill::create([
            'utility_type' => $type,
            'billing_period' => $period,
            'amount' => $amount,
            'usage_kwh_m3' => $usage,
            'is_paid' => true,
        ]);

        // Calculate estimated carbon footprint (e.g. 0.85 kg CO2 per kWh)
        if ($type === 'electricity' && $usage > 0) {
            CarbonEmission::create([
                'period' => $period,
                'co2_kg' => $usage * 0.85,
                'source_category' => 'electricity',
            ]);
        }

        return $bill;
    }
}
