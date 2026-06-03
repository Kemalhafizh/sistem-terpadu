<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnnouncementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'content' => $this->faker->paragraph(3),
            'image_path' => null,
            'target_role' => null,
            'target_classroom_id' => null,
            'priority' => $this->faker->randomElement(['low', 'normal', 'urgent']),
            'is_active' => true,
            'expires_at' => null,
            'created_by' => User::factory()->state(['role' => 'admin']),
        ];
    }
}
