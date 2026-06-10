<?php

namespace Database\Factories;

use App\Models\CounselingSession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CounselingSessionFactory extends Factory
{
    protected $model = CounselingSession::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'counselor_id' => User::factory()->state(['role' => 'bk']),
            'requested_by' => User::factory(),
            'scheduled_at' => $this->faker->dateTimeBetween('now', '+1 week'),
            'started_at' => null,
            'ended_at' => null,
            'type' => $this->faker->randomElement(['individual', 'group', 'home_visit', 'conference']),
            'category' => $this->faker->randomElement(['akademik', 'sosial', 'karir', 'pribadi']),
            'status' => 'scheduled',
            'reason' => $this->faker->sentence(10),
            'session_notes' => null,
            'action_plan' => null,
            'follow_up_notes' => null,
            'follow_up_date' => null,
            'is_confidential' => true,
        ];
    }
}
