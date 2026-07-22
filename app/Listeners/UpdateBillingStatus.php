<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateBillingStatus implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(PaymentReceived $event): void
    {
        Log::info("Updating billing status for invoice {$event->invoiceNumber} amount Rp {$event->amount}");
    }
}
