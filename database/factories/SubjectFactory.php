<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'code' => strtoupper($this->faker->bothify('SUB-###')),
            'category' => 'Nasional',
            'kkm' => 75,
        ];
    }
}
