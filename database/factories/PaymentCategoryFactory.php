<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['SPP Bulanan', 'Uang Gedung', 'Seragam']),
            'default_amount' => $this->faker->numberBetween(100000, 1000000),
            'is_monthly' => $this->faker->boolean,
        ];
    }
}
