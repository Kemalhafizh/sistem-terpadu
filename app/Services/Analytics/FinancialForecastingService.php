<?php

namespace App\Services\Analytics;

use App\Models\FinancialForecast;

class FinancialForecastingService
{
    public function runSimulation(string $scenarioName, float $revenue, float $expense, int $creatorId, array $parameters = []): FinancialForecast
    {
        return FinancialForecast::create([
            'scenario_name' => $scenarioName,
            'projected_revenue' => $revenue,
            'projected_expense' => $expense,
            'confidence_interval' => 95.0,
            'parameters' => $parameters,
            'created_by' => $creatorId,
        ]);
    }
}
