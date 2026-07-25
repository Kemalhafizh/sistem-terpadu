<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'code' => 'SUB-' . strtoupper($this->faker->unique()->bothify('??####')),
            'category' => 'Nasional',
            'kkm' => 75,
        ];
    }
}
