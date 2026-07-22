<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogJournalEntry implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'low';

    public function handle(PaymentReceived $event): void
    {
        Log::info("Logging double-entry journal line for payment {$event->invoiceNumber} amount Rp {$event->amount}");
    }
}
