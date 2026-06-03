<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'student']),
            'classroom_id' => Classroom::factory(),
            'nis' => $this->faker->unique()->numerify('NIS-####'),
            'nisn' => $this->faker->unique()->numerify('##########'),
            'gender' => $this->faker->randomElement(['L', 'P']),
            'address' => $this->faker->address,
        ];
    }
}
