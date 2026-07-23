<?php

namespace App\Jobs;

use App\Services\VendorSlaProbeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProbeVendorSlaJob implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue('low');
    }

    public function handle(VendorSlaProbeService $probeService): void
    {
        Log::info('Vendor SLA Worker: Memulai pengujian otomatis ketersediaan & latensi vendor...');

        $metrics = $probeService->probeAllVendors();

        Log::info('Vendor SLA Worker: Pengujian vendor selesai.', [
            'vendor_count' => count($metrics),
        ]);
    }
}
