<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GeneratePdfReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $backoff = 60;

    public function __construct(
        public string $reportType,
        public int $studentId
    ) {
        $this->queue = 'low';
    }

    public function handle(): void
    {
        Log::info("Executing Low Priority PDF Report Generation Job for type '{$this->reportType}' student #{$this->studentId}");
    }
}
