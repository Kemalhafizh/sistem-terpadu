<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\PointCategory;
use App\Models\Student;
use App\Models\StudentPointLog;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $students = Student::with('classroom')->get();
        $guruBk = User::where('email', 'guru.bk@sultanagung1.sch.id')->first();

        // --- Attendances (7 days) ---
        for ($d = 6; $d >= 0; $d--) {
            $date = now()->subDays($d)->format('Y-m-d');
            // Skip weekends
            $dayOfWeek = now()->subDays($d)->dayOfWeek;
            if ($dayOfWeek === 0 || $dayOfWeek === 6) {
                continue;
            }

            foreach ($students as $student) {
                if (! $student->classroom) {
                    continue;
                }
                $rand = rand(1, 100);
                $status = $rand <= 85 ? 'H' : ($rand <= 90 ? 'S' : ($rand <= 95 ? 'I' : 'A'));

                Attendance::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $activeYear->id,
                    'classroom_id' => $student->classroom_id,
                    'date' => $date,
                    'status' => $status,
                    'recorded_by' => $student->classroom->teacher_id ?? 1,
                ]);
            }
        }

        // --- Grades (for first 12 students, 6 subjects, 4 types) ---
        $subjects = Subject::whereIn('code', ['MTK', 'FIS', 'BIG', 'BIN', 'PAI', 'THF'])->get();
        $guruMap = [
            'MTK' => User::where('email', 'guru.matematika@sultanagung1.sch.id')->first()->id,
            'FIS' => User::where('email', 'guru.fisika@sultanagung1.sch.id')->first()->id,
            'BIG' => User::where('email', 'guru.bahasa@sultanagung1.sch.id')->first()->id,
            'BIN' => User::where('email', 'guru.bahasa@sultanagung1.sch.id')->first()->id,
            'PAI' => User::where('email', 'guru.tahfidz@sultanagung1.sch.id')->first()->id,
            'THF' => User::where('email', 'guru.tahfidz@sultanagung1.sch.id')->first()->id,
        ];

        $targetStudents = $students->take(12);
        foreach ($targetStudents as $student) {
            foreach ($subjects as $subject) {
                foreach (['tugas', 'uh', 'pts', 'pas'] as $type) {
                    Grade::create([
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'academic_year_id' => $activeYear->id,
                        'type' => $type,
                        'score' => rand(65, 98),
                        'recorded_by' => $guruMap[$subject->code] ?? 1,
                    ]);
                }
            }
        }

        // --- Point Categories ---
        $categories = [
            ['name' => 'Terlambat Masuk Kelas', 'type' => 'penalty', 'default_points' => 5],
            ['name' => 'Bolos Pelajaran', 'type' => 'penalty', 'default_points' => 15],
            ['name' => 'Seragam Tidak Lengkap', 'type' => 'penalty', 'default_points' => 10],
            ['name' => 'Membawa HP Tanpa Izin', 'type' => 'penalty', 'default_points' => 20],
            ['name' => 'Berkelahi', 'type' => 'penalty', 'default_points' => 50],
            ['name' => 'Juara Lomba Tingkat Kota', 'type' => 'reward', 'default_points' => -30],
            ['name' => 'Hafalan Al-Quran 1 Juz', 'type' => 'reward', 'default_points' => -20],
            ['name' => 'Perilaku Terpuji', 'type' => 'reward', 'default_points' => -10],
        ];
        foreach ($categories as $c) {
            PointCategory::create($c);
        }

        // --- Student Point Logs (20 random entries) ---
        $pointCats = PointCategory::all();
        $randomStudents = $students->random(min(10, $students->count()));
        foreach ($randomStudents as $student) {
            $cat = $pointCats->random();
            StudentPointLog::create([
                'student_id' => $student->id,
                'created_by' => $guruBk->id,
                'points' => $cat->default_points,
                'type' => $cat->type,
                'point_category_id' => $cat->id,
                'description' => $cat->name.' - '.['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'][rand(0, 4)].' minggu ini',
            ]);
            // Add a second log for some
            if (rand(0, 1)) {
                $cat2 = $pointCats->random();
                StudentPointLog::create([
                    'student_id' => $student->id,
                    'created_by' => $guruBk->id,
                    'points' => $cat2->default_points,
                    'type' => $cat2->type,
                    'point_category_id' => $cat2->id,
                    'description' => $cat2->name.' - Insiden bulan lalu',
                ]);
            }
        }
    }
}
