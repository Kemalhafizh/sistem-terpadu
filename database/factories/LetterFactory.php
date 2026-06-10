<?php

namespace Database\Factories;

use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LetterFactory extends Factory
{
    protected $model = Letter::class;

    public function definition(): array
    {
        return [
            'template_id' => LetterTemplate::factory(),
            'letter_number' => null,
            'field_values' => ['keperluan' => 'Mendaftar Beasiswa'],
            'student_id' => Student::factory(),
            'employee_id' => null,
            'created_by' => User::factory()->state(['role' => 'staf_tu']),
            'issue_date' => $this->faker->date(),
            'status' => 'draft',
            'notes' => $this->faker->sentence(),
        ];
    }

    public function issued(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'issued',
            'letter_number' => '001/SKA/SMA-ISA/V/2026',
        ]);
    }
}
