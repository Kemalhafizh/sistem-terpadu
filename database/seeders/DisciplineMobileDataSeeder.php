<?php

namespace Database\Seeders;

use App\Models\PointCategory;
use App\Models\Student;
use App\Models\StudentPointLog;
use Illuminate\Database\Seeder;

class DisciplineMobileDataSeeder extends Seeder
{
    public function run(): void
    {
        $student = Student::first();
        
        if (!$student) return;

        // Ensure parent exists
        $admin = \App\Models\User::where('role', 'admin')->first();
        if (!$admin) $admin = \App\Models\User::factory()->create(['role' => 'admin']);

        // Seed some point categories if not exist
        $bolos = PointCategory::updateOrCreate(
            ['name' => 'Bolos Sekolah'],
            ['type' => 'penalty', 'default_points' => 30, 'is_active' => true]
        );

        $terlambat = PointCategory::updateOrCreate(
            ['name' => 'Terlambat Masuk Kelas'],
            ['type' => 'penalty', 'default_points' => 10, 'is_active' => true]
        );

        $merokok = PointCategory::updateOrCreate(
            ['name' => 'Merokok / Narkoba'],
            ['type' => 'penalty', 'default_points' => 75, 'is_active' => true]
        );

        $prestasi = PointCategory::updateOrCreate(
            ['name' => 'Juara Kelas / Lomba'],
            ['type' => 'reward', 'default_points' => -20, 'is_active' => true]
        );

        // Assign some points to trigger SP (50 points = SP1)
        StudentPointLog::create([
            'student_id' => $student->id,
            'point_category_id' => $bolos->id,
            'created_by' => $admin->id,
            'points' => 30,
            'type' => 'penalty',
            'description' => 'Bolos pada hari Senin.',
            'created_at' => now()->subDays(5),
        ]);

        StudentPointLog::create([
            'student_id' => $student->id,
            'point_category_id' => $terlambat->id,
            'created_by' => $admin->id,
            'points' => 10,
            'type' => 'penalty',
            'description' => 'Terlambat upacara.',
            'created_at' => now()->subDays(3),
        ]);

        StudentPointLog::create([
            'student_id' => $student->id,
            'point_category_id' => $terlambat->id,
            'created_by' => $admin->id,
            'points' => 10,
            'type' => 'penalty',
            'description' => 'Terlambat lagi.',
            'created_at' => now()->subDays(1),
        ]);
        // Total should be 50 points now -> triggers SP1 automatically via model booted hook.
    }
}
