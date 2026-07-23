<?php

namespace App\Jobs;

use App\Services\DisasterRecoveryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CreateDatabaseSnapshotJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $label = 'scheduled')
    {
        $this->onQueue('high');
    }

    public function handle(DisasterRecoveryService $drService): void
    {
        Log::info("Disaster Recovery: Memulai pembuatan snapshot database [{$this->label}]...");

        $meta = $drService->createSnapshot($this->label);

        Log::info("Disaster Recovery: Snapshot database [{$meta['filename']}] berhasil dibuat dengan Checksum SHA-256 [{$meta['checksum_sha256']}].");
    }
}
