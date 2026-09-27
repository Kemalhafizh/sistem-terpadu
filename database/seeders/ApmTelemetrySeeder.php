<?php

namespace Database\Seeders;

use App\Services\ApmTelemetryService;
use Illuminate\Database\Seeder;

class ApmTelemetrySeeder extends Seeder
{
    public function run(): void
    {
        // Smoke check only: the service must build without exceptions. It no
        // longer returns fixed numbers, so there is nothing to assert about the
        // values — on a fresh install with no recorded requests they are null.
        $metrics = app(ApmTelemetryService::class)->getTelemetryMetrics();

        assert(array_key_exists('p99', $metrics['latency']));
    }
}
