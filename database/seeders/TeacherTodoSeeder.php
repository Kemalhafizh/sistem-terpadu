<?php

namespace Database\Seeders;

use App\Models\TeacherTodo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TeacherTodoSeeder extends Seeder
{
    /**
     * Seed realistic teacher todo items for demo purposes.
     * Creates ~40 todo items spread across all guru users.
     */
    public function run(): void
    {
        $teachers = User::where('role', 'guru')->get();

        if ($teachers->isEmpty()) {
            return;
        }

        $todosPerTeacher = [
            // ── Upcoming / Active Todos ──────────────────────────────────
            [
                'title' => 'Siapkan RPP Matematika Kelas X Bab 5: Trigonometri',
                'description' => 'Menyusun RPP lengkap termasuk indikator pencapaian, langkah-langkah pembelajaran, dan lembar penilaian untuk materi trigonometri dasar.',
                'priority' => 'high',
                'category' => 'mengajar',
                'due_date' => Carbon::now()->addDays(2),
                'is_completed' => false,
            ],
            [
                'title' => 'Koreksi tugas siswa Bab 3: Persamaan Linear',
                'description' => 'Mengoreksi 30 lembar jawaban tugas mandiri siswa kelas X-1 tentang persamaan linear dua variabel.',
                'priority' => 'high',
                'category' => 'penilaian',
                'due_date' => Carbon::now(),
                'is_completed' => false,
            ],
            [
                'title' => 'Upload nilai harian ke sistem SISTA',
                'description' => 'Input nilai ulangan harian Bab 4 ke modul penilaian di SISTA untuk kelas X-1 dan X-2.',
                'priority' => 'medium',
                'category' => 'administrasi',
                'due_date' => Carbon::now()->addDay(),
                'is_completed' => false,
            ],
            [
                'title' => 'Rapat koordinasi wali kelas bulan Juni',
                'description' => 'Persiapan agenda rapat bulanan wali kelas: evaluasi kehadiran siswa, progress kurikulum, dan masalah kedisiplinan.',
                'priority' => 'medium',
                'category' => 'rapat',
                'due_date' => Carbon::now()->addDays(3),
                'is_completed' => false,
            ],
            [
                'title' => 'Lengkapi jurnal mengajar minggu ini',
                'description' => 'Isi jurnal mengajar digital untuk pertemuan hari Senin-Rabu di modul Teaching Journal.',
                'priority' => 'low',
                'category' => 'administrasi',
                'due_date' => Carbon::now()->addDays(4),
                'is_completed' => false,
            ],
            [
                'title' => 'Buat soal UAS Semester Genap',
                'description' => 'Menyusun 40 soal pilihan ganda dan 5 soal esai untuk UAS Semester Genap materi Bab 1-5.',
                'priority' => 'high',
                'category' => 'penilaian',
                'due_date' => Carbon::now()->addDays(7),
                'is_completed' => false,
            ],

            // ── Overdue Todos ────────────────────────────────────────────
            [
                'title' => 'Kumpulkan laporan piket harian bulan lalu',
                'description' => 'Mengumpulkan rekapitulasi piket harian ke Wakil Kepala Sekolah bidang Kurikulum.',
                'priority' => 'medium',
                'category' => 'administrasi',
                'due_date' => Carbon::now()->subDays(2),
                'is_completed' => false,
            ],

            // ── Completed Todos ──────────────────────────────────────────
            [
                'title' => 'Siapkan materi presentasi parent-teacher conference',
                'description' => 'Slide presentasi untuk rapat orang tua/wali murid kelas X-1 telah selesai dibuat.',
                'priority' => 'high',
                'category' => 'rapat',
                'due_date' => Carbon::now()->subDays(3),
                'is_completed' => true,
                'completed_at' => Carbon::now()->subDays(3),
            ],
            [
                'title' => 'Review kurikulum semester genap Bab 1-3',
                'description' => 'Evaluasi efektivitas kurikulum dan capaian pembelajaran siswa untuk Bab 1-3 semester genap.',
                'priority' => 'medium',
                'category' => 'mengajar',
                'due_date' => Carbon::now()->subDays(5),
                'is_completed' => true,
                'completed_at' => Carbon::now()->subDays(5),
            ],
            [
                'title' => 'Input nilai tugas kelompok Bab 2',
                'description' => 'Seluruh nilai tugas kelompok kelas X-1 dan X-2 sudah diinput ke sistem.',
                'priority' => 'low',
                'category' => 'penilaian',
                'due_date' => Carbon::now()->subDays(7),
                'is_completed' => true,
                'completed_at' => Carbon::now()->subDays(6),
            ],
        ];

        foreach ($teachers as $teacher) {
            foreach ($todosPerTeacher as $todoData) {
                TeacherTodo::create(array_merge($todoData, [
                    'user_id' => $teacher->id,
                ]));
            }
        }

        // Add some extra random todos for variety
        foreach ($teachers->take(3) as $teacher) {
            TeacherTodo::factory()->count(3)->create([
                'user_id' => $teacher->id,
            ]);
        }
    }
}
