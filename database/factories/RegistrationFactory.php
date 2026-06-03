<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegistrationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'registration_number' => 'REG-'.$this->faker->unique()->numberBetween(1000, 9999),
            'full_name' => $this->faker->name,
            'nisn' => $this->faker->unique()->numerify('##########'),
            'previous_school' => $this->faker->company.' School',
            'parent_phone' => $this->faker->phoneNumber,
            'status' => 'pending',
            'academic_year_id' => AcademicYear::factory(),
        ];
    }
}
