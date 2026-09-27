<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherTodoFactory extends Factory
{
    public function definition(): array
    {
        $isCompleted = $this->faker->boolean(30);

        return [
            'user_id' => User::factory()->state(['role' => 'guru']),
            'title' => $this->faker->randomElement([
                'Siapkan RPP Matematika Kelas X',
                'Koreksi tugas siswa Bab 3',
                'Buat soal UTS Fisika',
                'Upload nilai harian ke sistem',
                'Rapat koordinasi wali kelas',
                'Persiapan materi remedial',
                'Lengkapi jurnal mengajar minggu ini',
                'Buat laporan perkembangan siswa',
                'Siapkan presentasi parent-teacher conference',
                'Review kurikulum semester genap',
                'Input nilai tugas kelompok',
                'Susun modul pembelajaran daring',
                'Evaluasi program remedial Bab 5',
                'Persiapan rapat evaluasi akhir bulan',
                'Kumpulkan laporan piket harian',
            ]),
            'description' => $this->faker->optional(0.6)->paragraph(),
            'due_date' => $this->faker->optional(0.7)->dateTimeBetween('-3 days', '+14 days'),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            'category' => $this->faker->randomElement(['mengajar', 'administrasi', 'rapat', 'penilaian', 'lainnya']),
            'is_completed' => $isCompleted,
            'completed_at' => $isCompleted ? $this->faker->dateTimeBetween('-7 days', 'now') : null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'is_completed' => false,
            'completed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'is_completed' => false,
            'completed_at' => null,
            'due_date' => now()->subDays(rand(1, 5)),
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn () => [
            'priority' => 'high',
        ]);
    }

    public function dueToday(): static
    {
        return $this->state(fn () => [
            'is_completed' => false,
            'completed_at' => null,
            'due_date' => now()->toDateString(),
        ]);
    }
}
