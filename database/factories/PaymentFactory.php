<?php

namespace Database\Factories;

use App\Models\Billing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'billing_id' => Billing::factory(),
            'created_by' => User::factory()->state(['role' => 'admin']),
            'amount_paid' => $this->faker->numberBetween(100000, 1000000),
            'payment_date' => $this->faker->date(),
            'payment_method' => $this->faker->randomElement(['cash', 'transfer']),
            'receipt_number' => $this->faker->unique()->numerify('RCP-####'),
        ];
    }
}
