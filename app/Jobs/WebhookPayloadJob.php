<?php

namespace App\Jobs;

use App\Models\WebhookSubscription;
use App\Services\WebhookManagerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class WebhookPayloadJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public WebhookSubscription $subscription,
        public string $eventType,
        public array $payload
    ) {
        $this->onQueue('high');
    }

    public function handle(WebhookManagerService $webhookManager): void
    {
        Log::info("Webhook Dispatcher: Pengiriman event [{$this->eventType}] ke [{$this->subscription->target_url}]...");

        $delivery = $webhookManager->deliver($this->subscription, $this->eventType, $this->payload);

        Log::info("Webhook Dispatcher: Pengiriman selesai dengan status [{$delivery->status}] (HTTP {$delivery->response_code}).");
    }
}
