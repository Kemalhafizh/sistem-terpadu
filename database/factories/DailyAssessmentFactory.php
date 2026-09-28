<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\DailyAssessment;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DailyAssessmentFactory extends Factory
{
    protected $model = DailyAssessment::class;

    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'classroom_id' => Classroom::factory(),
            'teacher_id' => User::factory()->state(['role' => 'guru']),
            'academic_year_id' => AcademicYear::factory(),
            'title' => $this->faker->sentence(3),
            'type' => $this->faker->randomElement(['ulangan_harian', 'tugas', 'kuis']),
            'assessment_date' => $this->faker->date(),
            'max_score' => 100.00,
            'kkm' => 75.00,
            'description' => $this->faker->sentence(10),
        ];
    }
}
