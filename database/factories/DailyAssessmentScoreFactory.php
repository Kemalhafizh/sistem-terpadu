<?php

namespace Database\Factories;

use App\Models\DailyAssessment;
use App\Models\DailyAssessmentScore;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class DailyAssessmentScoreFactory extends Factory
{
    protected $model = DailyAssessmentScore::class;

    public function definition(): array
    {
        $score = $this->faker->randomFloat(2, 50, 100);
        $needsRemedial = $score < 75;

        return [
            'daily_assessment_id' => DailyAssessment::factory(),
            'student_id' => Student::factory(),
            'score' => $score,
            'needs_remedial' => $needsRemedial,
            'remedial_score' => $needsRemedial ? $this->faker->randomFloat(2, 75, 85) : null,
            'remedial_status' => $needsRemedial ? $this->faker->randomElement(['none', 'scheduled', 'completed']) : 'none',
            'notes' => $this->faker->sentence(5),
        ];
    }
}
