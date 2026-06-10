<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement(['it', 'sarpras', 'keuangan', 'akademik']),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'status' => 'open',
            'reporter_id' => User::factory()->state(['role' => 'guru']),
            'assignee_id' => null,
            'attachment_path' => null,
            'resolution' => null,
            'resolved_at' => null,
            'closed_at' => null,
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'assignee_id' => User::factory()->state(['role' => 'staf_tu']),
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'assignee_id' => User::factory()->state(['role' => 'staf_tu']),
            'resolution' => $this->faker->paragraph(),
            'resolved_at' => now(),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'assignee_id' => User::factory()->state(['role' => 'staf_tu']),
            'resolution' => $this->faker->paragraph(),
            'resolved_at' => now()->subHour(),
            'closed_at' => now(),
        ]);
    }
}
