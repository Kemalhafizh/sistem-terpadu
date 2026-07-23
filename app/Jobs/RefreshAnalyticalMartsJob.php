<?php

namespace App\Jobs;

use App\Services\EdwOlapEngineService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RefreshAnalyticalMartsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public ?string $date = null)
    {
        $this->onQueue('low');
    }

    public function handle(EdwOlapEngineService $olapService): void
    {
        $targetDate = $this->date ?? now()->toDateString();
        Log::info("EDW OLAP Worker: Memulai kalkulasi pra-kalkulasi data mart untuk [{$targetDate}]...");

        $summary = $olapService->refreshAllMarts($targetDate);

        Log::info("EDW OLAP Worker: Refresh data mart selesai untuk [{$targetDate}].", $summary);
    }
}
