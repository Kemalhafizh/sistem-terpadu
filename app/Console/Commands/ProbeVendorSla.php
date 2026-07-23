<?php

namespace App\Console\Commands;

use App\Services\VendorSlaProbeService;
use Illuminate\Console\Command;

class ProbeVendorSla extends Command
{
    protected $signature = 'vendor:probe-sla';

    protected $description = 'Jalankan pengujian ketersediaan & latensi vendor pihak ketiga (Fonnte, Midtrans, Dapodik, EMIS)';

    public function handle(VendorSlaProbeService $probeService): int
    {
        $this->info("Memulai pengujian SLA Vendor Pihak Ketiga...");

        $results = $probeService->probeAllVendors();

        foreach ($results as $key => $metric) {
            $this->line("- [{$metric->vendor_name}]: Status {$metric->status} (Latensi: {$metric->latency_ms} ms)");
        }

        $this->info("Pengujian SLA Vendor Selesai.");

        return self::SUCCESS;
    }
}
