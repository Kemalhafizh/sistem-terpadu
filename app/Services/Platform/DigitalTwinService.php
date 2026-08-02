<?php

namespace App\Services\Platform;

use App\Models\DigitalTwinSimulation;

class DigitalTwinService
{
    public function runSimulation(string $name, string $parameters, array $outcomes, int $createdBy): DigitalTwinSimulation
    {
        return DigitalTwinSimulation::create([
            'scenario_name' => $name,
            'parameters' => $parameters,
            'predicted_outcomes' => $outcomes,
            'created_by' => $createdBy,
        ]);
    }
}
