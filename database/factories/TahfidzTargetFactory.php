<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\TahfidzTarget;
use Illuminate\Database\Eloquent\Factories\Factory;

class TahfidzTargetFactory extends Factory
{
    protected $model = TahfidzTarget::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'target_juz' => $this->faker->numberBetween(1, 30),
            'target_pages' => $this->faker->numberBetween(10, 100),
            'achieved_juz' => $this->faker->numberBetween(0, 5),
            'achieved_pages' => $this->faker->numberBetween(0, 50),
            'status' => $this->faker->randomElement(['on_track', 'behind', 'exceeded', 'completed']),
            'notes' => $this->faker->sentence(5),
        ];
    }
}
