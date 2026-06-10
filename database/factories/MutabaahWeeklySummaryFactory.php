<?php

namespace Database\Factories;

use App\Models\MutabaahWeeklySummary;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class MutabaahWeeklySummaryFactory extends Factory
{
    protected $model = MutabaahWeeklySummary::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'week_start' => now()->startOfWeek(),
            'week_end' => now()->endOfWeek(),
            'total_score' => $this->faker->numberBetween(60, 100),
            'sholat_5_waktu_count' => $this->faker->numberBetween(30, 35),
            'sholat_berjamaah_count' => $this->faker->numberBetween(10, 35),
            'tadarus_total_pages' => $this->faker->numberBetween(5, 30),
            'grade' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
            'wali_kelas_comment' => $this->faker->sentence(10),
            'parent_acknowledged' => $this->faker->boolean(),
        ];
    }
}
