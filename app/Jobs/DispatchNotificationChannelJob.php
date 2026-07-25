<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Services\Communication\NotificationDispatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchNotificationChannelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public NotificationLog $notificationLog
    ) {
        $this->onQueue('high');
    }

    public function handle(NotificationDispatchService $dispatchService): void
    {
        $dispatchService->deliverChannel($this->notificationLog);
    }
}
