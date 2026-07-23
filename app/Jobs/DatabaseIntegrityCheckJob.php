<?php

namespace App\Jobs;

use App\Services\DisasterRecoveryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class DatabaseIntegrityCheckJob implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue('default');
    }

    public function handle(DisasterRecoveryService $drService): void
    {
        Log::info('Disaster Recovery: Memulai audit otomatis integritas database...');

        $audit = $drService->runIntegrityAudit();

        if ($audit['status'] === 'HEALTHY') {
            Log::info('Disaster Recovery: Audit integritas database selesai - Status: HEALTHY');
        } else {
            Log::error('Disaster Recovery: PERINGATAN BENCANA DATA! Database mengalami kecacatan / ketidakcocokan foreign key.', $audit);
        }
    }
}
