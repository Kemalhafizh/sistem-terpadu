<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MicrolearningSeeder extends Seeder
{
    public function run()
    {
        $teacher = User::factory()->create(['name' => 'Guru SMA Islam Sultan Agung 1', 'role' => 'teacher']);
        $student = User::factory()->create(['name' => 'Siswa SMA Islam Sultan Agung 1', 'role' => 'student']);

        DB::table('micro_lessons')->insert([
            'title' => 'Dasar-Dasar Sejarah Kebudayaan Islam',
            'ai_generated_summary' => 'Ringkasan singkat SKI.',
            'estimated_minutes' => 10,
            'is_published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('gamified_quizzes')->insert([
            'teacher_id' => $teacher->id,
            'title' => 'Kuis Tajwid Mingguan',
            'join_code' => Str::random(6),
            'status' => 'waiting',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('memory_cards')->insert([
            'front_content' => 'Apa hukum nun mati bertemu ba?',
            'back_content' => 'Iqlab',
            'difficulty_level' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
