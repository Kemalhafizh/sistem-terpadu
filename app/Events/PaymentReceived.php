<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $student,
        public float $amount,
        public string $invoiceNumber,
        public string $paymentType = 'SPP'
    ) {}
}
