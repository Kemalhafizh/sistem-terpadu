<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassSession;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ClassSession>
 */
class ClassSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'schedule_id' => null,
            'teacher_id' => User::factory()->state(['role' => 'guru']),
            'classroom_id' => Classroom::factory(),
            'subject_id' => Subject::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'session_date' => now()->toDateString(),
            'jam_ke' => 1,
            'scheduled_start' => '07:00:00',
            'scheduled_end' => '07:45:00',
            'status' => ClassSession::STATUS_SCHEDULED,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => ClassSession::STATUS_ACTIVE,
            'actual_start' => now(),
            'qr_secret' => Str::random(64),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => ClassSession::STATUS_COMPLETED,
            'actual_start' => now()->subMinutes(45),
            'actual_end' => now(),
        ]);
    }
}
