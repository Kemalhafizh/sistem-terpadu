<?php

namespace Database\Factories;

use App\Models\EventAttendanceEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventAttendanceEventFactory extends Factory
{
    protected $model = EventAttendanceEvent::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true) . ' Event',
            'type' => $this->faker->randomElement(['upacara', 'sholat_dhuha', 'sholat_dzuhur', 'kultum', 'phbi', 'rapat_guru', 'osis', 'class_meeting', 'lainnya']),
            'event_date' => $this->faker->date(),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'target' => $this->faker->randomElement(['all_students', 'all_teachers', 'specific_class', 'specific_group']),
            'target_ids' => null,
            'created_by' => User::factory()->state(['role' => 'admin']),
            'notes' => $this->faker->sentence(10),
        ];
    }
}
