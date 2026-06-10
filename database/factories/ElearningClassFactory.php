<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\ElearningClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ElearningClassFactory extends Factory
{
    protected $model = ElearningClass::class;

    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'classroom_id' => Classroom::factory(),
            'teacher_id' => User::factory()->state(['role' => 'guru']),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(10),
            'is_archived' => false,
        ];
    }
}
