<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\TahfidzLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class TahfidzDetailSeeder extends Seeder
{
    public function run(): void
    {
        $guruTahfidz = User::where('email', 'guru.tahfidz@sultanagung1.sch.id')->first();
        if (!$guruTahfidz) {
            $guruTahfidz = User::where('role', 'guru')->first();
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
            [78, 'An-Naba', 40],
            [79, 'An-Naziat', 46],
            [80, 'Abasa', 42],
            [81, 'At-Takwir', 29],
            [82, 'Al-Infitar', 19],
            [83, 'Al-Mutaffifin', 36],
            [84, 'Al-Inshiqaq', 25],
            [85, 'Al-Buruj', 22],
            [86, 'At-Tariq', 17],
            [87, 'Al-A\'la', 19],
            [88, 'Al-Ghashiyah', 26],
            [89, 'Al-Fajr', 30],
            [90, 'Al-Balad', 20],
        ];

        TahfidzLog::truncate();

        foreach ($tahfidzStudents as $student) {
            // Seed 15-20 setoran logs for each student over the last 60 days
            $logCount = rand(15, 20);
            $currentDate = now()->subDays(60);

            for ($i = 0; $i < $logCount; $i++) {
                $surahIdx = rand(0, count($surahs) - 1);
                $surah = $surahs[$surahIdx];
                
                $fromAyat = rand(1, max(1, $surah[2] - 10));
                $toAyat = min($surah[2], $fromAyat + rand(5, 10));

                TahfidzLog::create([
                    'student_id' => $student->id,
                    'teacher_id' => $guruTahfidz->id,
                    'surah_number' => $surah[0],
                    'surah_name' => $surah[1],
                    'from_ayat' => $fromAyat,
                    'to_ayat' => $toAyat,
                    'type' => rand(1, 100) <= 65 ? 'ziyadah' : 'murajaah',
                    'grade' => ['A', 'B', 'B', 'C'][rand(0, 3)],
                    'notes' => ['Tajwid sangat baik', 'Lancar, teruskan', 'Perlu mengulang makhorijul huruf', 'Hafalan cukup baik'][rand(0, 3)],
                    'date' => $currentDate->format('Y-m-d'),
                ]);

                // Increment date to simulate progress
                $currentDate = $currentDate->addDays(rand(2, 4));
            }
        }
    }
}
