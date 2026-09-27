<?php

namespace Database\Factories;

use App\Models\LibraryBook;
use App\Models\LibraryLoan;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LibraryLoanFactory extends Factory
{
    protected $model = LibraryLoan::class;

    public function definition(): array
    {
        $borrowedAt = $this->faker->dateTimeBetween('-1 month', 'now');
        $dueAt = (clone $borrowedAt)->modify('+7 days');

        return [
            'student_id' => Student::factory(),
            'library_book_id' => LibraryBook::factory(),
            'borrowed_at' => $borrowedAt,
            'due_at' => $dueAt,
            'returned_at' => null,
            'status' => 'borrowed',
            'fine_amount' => 0.00,
            'fine_paid_at' => null,
            'created_by' => User::factory()->state(['role' => 'staf_tu']),
        ];
    }

    public function returned(): static
    {
        return $this->state(function (array $attributes) {
            $borrowedAt = $attributes['borrowed_at'] instanceof \DateTimeInterface
                ? $attributes['borrowed_at']
                : new \DateTime($attributes['borrowed_at']);
            $returnedAt = (clone $borrowedAt)->modify('+'.rand(1, 6).' days');

            return [
                'returned_at' => $returnedAt,
                'status' => 'returned',
            ];
        });
    }

    public function overdue(): static
    {
        return $this->state(function (array $attributes) {
            $borrowedAt = $this->faker->dateTimeBetween('-1 month', '-10 days');
            $dueAt = (clone $borrowedAt)->modify('+7 days');

            return [
                'borrowed_at' => $borrowedAt,
                'due_at' => $dueAt,
                'returned_at' => null,
                'status' => 'overdue',
            ];
        });
    }
}
