<?php

namespace Database\Seeders;

use App\Services\CircuitBreakerService;
use Illuminate\Database\Seeder;

class CircuitBreakerSeeder extends Seeder
{
    public function run(): void
    {
        $cb = new CircuitBreakerService();

        // Ensure all circuits initialized to CLOSED
        $cb->resetCircuit('fonnte_whatsapp');
        $cb->resetCircuit('midtrans_payment');
        $cb->resetCircuit('kemendikbud_dapodik');
    }
}
