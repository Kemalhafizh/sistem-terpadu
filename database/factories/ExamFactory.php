<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamFactory extends Factory
{
    protected $model = Exam::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3).' Exam',
            'subject_id' => Subject::factory(),
            'classroom_id' => Classroom::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'created_by' => User::factory()->state(['role' => 'guru']),
            'type' => $this->faker->randomElement(['ulangan_harian', 'uts', 'uas']),
            'start_time' => now()->subDays(1),
            'end_time' => now()->addDays(1),
            'duration_minutes' => 60,
            'total_questions' => 10,
            'shuffle_questions' => false,
            'shuffle_options' => false,
            'is_secure_mode' => false,
            'max_violations' => 3,
            'passing_score' => 75.00,
            'status' => 'published',
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }
}
