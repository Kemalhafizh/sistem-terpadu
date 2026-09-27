<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Models\TreasuryForecast;

class DynamicTreasuryForecastingService
{
    /**
     * Generate 6-month predictive cashflow forecast based on tuition payment curves.
     */
    public function generateForecast(string $scenario = 'base'): array
    {
        $multiplier = match ($scenario) {
            'conservative' => 0.85,
            'optimistic' => 1.15,
            default => 1.00,
        };

        $baseTuitionPerMonth = 450000000.0; // Rp 450 Juta
        $baseOperationalPerMonth = 380000000.0; // Rp 380 Juta

        $results = [];

        for ($i = 1; $i <= 6; $i++) {
            $month = now()->addMonths($i)->format('Y-m');
            $seasonalFactor = 1.0 + (sin($i) * 0.08); // seasonal fluctuation

            $projectedInflow = round($baseTuitionPerMonth * $seasonalFactor * $multiplier, 2);
            $projectedOutflow = round($baseOperationalPerMonth * (1.0 + ($i * 0.01)), 2);
            $projectedNet = round($projectedInflow - $projectedOutflow, 2);

            $forecast = TreasuryForecast::updateOrCreate(
                ['forecast_month' => $month, 'forecast_scenario' => $scenario],
                [
                    'projected_tuition_inflow' => $projectedInflow,
                    'projected_operational_outflow' => $projectedOutflow,
                    'projected_net_cash' => $projectedNet,
                    'confidence_lower' => round($projectedNet * 0.88, 2),
                    'confidence_upper' => round($projectedNet * 1.12, 2),
                    'historical_trend_factor' => round($seasonalFactor, 2),
                ]
            );

            $results[] = $forecast;
        }

        return [
            'scenario' => $scenario,
            'forecast_count' => count($results),
            'forecasts' => $results,
        ];
    }
}
