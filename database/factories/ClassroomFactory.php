<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassroomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['X', 'XI', 'XII']).'-'.$this->faker->randomElement(['IPA', 'IPS']).'-'.$this->faker->unique()->numberBetween(1, 10000),
            'grade_level' => $this->faker->randomElement(['X', 'XI', 'XII']),
            'major' => $this->faker->randomElement(['IPA', 'IPS']),
            'academic_year' => '2024/2025',
            'teacher_id' => User::factory()->state(['role' => 'guru']),
        ];
    }
}
