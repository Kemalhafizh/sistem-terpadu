<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicYearFactory extends Factory
{
    public function definition(): array
    {
        $year = $this->faker->unique()->year;

        return [
            'year' => "$year/".($year + 1),
            'semester' => 'Ganjil',
            'is_active' => false,
        ];
    }
}
