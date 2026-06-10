<?php

namespace Database\Factories;

use App\Models\EventAttendanceEvent;
use App\Models\EventAttendanceRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventAttendanceRecordFactory extends Factory
{
    protected $model = EventAttendanceRecord::class;

    public function definition(): array
    {
        return [
            'event_id' => EventAttendanceEvent::factory(),
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['hadir', 'sakit', 'izin', 'alpa']),
            'check_in_time' => '08:05:00',
            'notes' => $this->faker->sentence(3),
        ];
    }
}
