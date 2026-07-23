<?php

namespace Database\Seeders;

use App\Services\VendorSlaProbeService;
use Illuminate\Database\Seeder;

class VendorSlaSeeder extends Seeder
{
    public function run(): void
    {
        /** @var VendorSlaProbeService $probeService */
        $probeService = app(VendorSlaProbeService::class);
        $probeService->probeAllVendors();
    }
}
