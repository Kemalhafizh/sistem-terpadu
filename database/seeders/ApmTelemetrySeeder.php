<?php

namespace Database\Seeders;

use App\Services\ApmTelemetryService;
use Illuminate\Database\Seeder;

class ApmTelemetrySeeder extends Seeder
{
    public function run(): void
    {
        $service = new ApmTelemetryService();
        $metrics = $service->getTelemetryMetrics();

        // Verify telemetry metrics can be retrieved without exceptions
        assert($metrics['latency']['p99'] <= 100.0);
    }
}
