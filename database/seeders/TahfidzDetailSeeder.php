<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\TahfidzLog;
use App\Models\TahfidzTarget;
use App\Models\User;
use App\Services\TahfidzService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class TahfidzDetailSeeder extends Seeder
{
    public function run(): void
    {
        $guruTahfidz = User::where('email', 'guru.tahfidz@sultanagung1.sch.id')->first();
        if (! $guruTahfidz) {
            $guruTahfidz = User::where('role', 'guru')->first();
        }

        $activeYear = AcademicYear::current();
        if (! $activeYear) {
            $activeYear = AcademicYear::create([
                'year' => '2025/2026',
                'semester' => 'Genap',
                'is_active' => true,
                'start_date' => '2026-01-06',
                'end_date' => '2026-06-21',
            ]);
        }

        // We assume students in the XII-A class are the main tahfidz students
        $tahfidzStudents = Student::whereHas('classroom', function ($q) {
            $q->where('name', 'XII-A');
        })->get();

        // If XII-A students are not found, fallback to first 15 students
        if ($tahfidzStudents->isEmpty()) {
            $tahfidzStudents = Student::take(15)->get();
        }

        $surahs = [
            [78, 'An-Naba', 40, 30],
            [79, 'An-Naziat', 46, 30],
            [80, 'Abasa', 42, 30],
            [81, 'At-Takwir', 29, 30],
            [82, 'Al-Infitar', 19, 30],
            [83, 'Al-Mutaffifin', 36, 30],
            [84, 'Al-Inshiqaq', 25, 30],
            [85, 'Al-Buruj', 22, 30],
            [86, 'At-Tariq', 17, 30],
            [87, 'Al-A\'la', 19, 30],
            [88, 'Al-Ghashiyah', 26, 30],
            [89, 'Al-Fajr', 30, 30],
            [90, 'Al-Balad', 20, 30],
        ];

        // Disable foreign key checks for clean truncation
        Schema::disableForeignKeyConstraints();
        TahfidzLog::truncate();
        TahfidzTarget::truncate();
        Schema::enableForeignKeyConstraints();

        $tahfidzService = app(TahfidzService::class);

        foreach ($tahfidzStudents as $student) {
            // Seed a target for each student
            $targetJuz = rand(2, 4);
            $targetPages = $targetJuz * 20; // 1 Juz = 20 pages

            TahfidzTarget::create([
                'student_id' => $student->id,
                'academic_year_id' => $activeYear->id,
                'target_juz' => $targetJuz,
                'target_pages' => $targetPages,
                'achieved_juz' => 0,
                'achieved_pages' => 0,
                'status' => 'on_track',
                'notes' => 'Tingkatkan kualitas bacaan tajwid dan hafalan baru.',
            ]);

            // Seed 10-15 setoran logs for each student over the last 60 days
            $logCount = rand(10, 15);
            $currentDate = now()->subDays(60);

            for ($i = 0; $i < $logCount; $i++) {
                $surahIdx = rand(0, count($surahs) - 1);
                $surah = $surahs[$surahIdx];

                $fromAyat = rand(1, max(1, $surah[2] - 10));
                $toAyat = min($surah[2], $fromAyat + rand(3, 8));

                // Calculate pages count
                $pages = rand(1, 2);

                TahfidzLog::create([
                    'student_id' => $student->id,
                    'teacher_id' => $guruTahfidz->id,
                    'surah_number' => $surah[0],
                    'surah_name' => $surah[1],
                    'from_ayat' => $fromAyat,
                    'to_ayat' => $toAyat,
                    'type' => rand(1, 100) <= 70 ? 'ziyadah' : 'murajaah',
                    'grade' => ['A', 'B', 'B', 'C'][rand(0, 3)],
                    'notes' => ['Tajwid sangat baik', 'Lancar, teruskan', 'Perlu mengulang makhorijul huruf', 'Hafalan cukup baik'][rand(0, 3)],
                    'date' => $currentDate->format('Y-m-d'),
                    'juz' => $surah[3],
                    'pages_count' => $pages,
                ]);

                // Increment date to simulate progress
                $currentDate = $currentDate->copy()->addDays(rand(2, 4));
            }

            // Recalculate target accomplishments and status
            $tahfidzService->updateTargetAchievement($student, $activeYear);
        }
    }
}
