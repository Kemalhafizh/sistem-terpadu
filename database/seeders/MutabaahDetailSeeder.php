<?php

namespace Database\Seeders;

use App\Models\MutabaahItem;
use App\Models\MutabaahLog;
use App\Models\MutabaahWeeklySummary;
use App\Models\Student;
use App\Services\MutabaahService;
use Illuminate\Database\Seeder;

class MutabaahDetailSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Basic Items (Keep for compatibility)
        $items = [
            ['name' => 'Shalat Subuh Berjamaah', 'category' => 'wajib'],
            ['name' => 'Shalat Dhuha', 'category' => 'sunnah'],
            ['name' => 'Tilawah Al-Quran (2+ Halaman)', 'category' => 'wajib'],
            ['name' => 'Puasa Sunnah', 'category' => 'sunnah'],
            ['name' => 'Sedekah Harian', 'category' => 'akhlak'],
            ['name' => 'Dzikir Pagi & Petang', 'category' => 'sunnah'],
        ];

        MutabaahItem::truncate();
        MutabaahLog::truncate();
        MutabaahWeeklySummary::truncate();

        foreach ($items as $item) {
            MutabaahItem::create([
                'name' => $item['name'],
                'category' => $item['category'],
            ]);
        }

        // Resolve MutabaahService
        $mutabaahService = app(MutabaahService::class);

        // Seeding detailed logs for first 30 students over the last 30 days
        $targetStudents = Student::take(30)->get();

        foreach ($targetStudents as $student) {
            $parentId = $student->parent_id;

            // Generate daily logs
            for ($d = 30; $d >= 0; $d--) {
                $date = now()->subDays($d);
                $dateStr = $date->format('Y-m-d');
                $dayOfWeek = $date->dayOfWeek; // 1 = Monday, 4 = Thursday

                // Determine realistic values
                $subuh = rand(1, 100) <= 80 ? rand(1, 2) : 0; // 80% pray, mostly berjamaah or munfarid
                $dzuhur = rand(1, 100) <= 90 ? 2 : 1; // mostly berjamaah at school
                $ashar = rand(1, 100) <= 90 ? rand(1, 2) : 0;
                $maghrib = rand(1, 100) <= 95 ? 2 : 1;
                $isya = rand(1, 100) <= 85 ? rand(1, 2) : 0;

                $dhuha = rand(1, 100) <= 40 ? 1 : 0; // 40% chance
                $tahajud = rand(1, 100) <= 20 ? 1 : 0; // 20% chance

                $tadarus = rand(0, 10) > 4 ? rand(1, 5) : 0; // 60% chance of reading 1-5 pages

                $puasa = false;
                if (in_array($dayOfWeek, [1, 4])) {
                    $puasa = rand(1, 100) <= 30; // 30% chance on Mon/Thu
                }

                $sedekah = rand(1, 100) <= 50; // 50% chance
                $dzikirPagi = rand(1, 100) <= 60;
                $dzikirSore = rand(1, 100) <= 50;

                $catatan = null;
                if (rand(1, 100) <= 15) {
                    $reflections = [
                        'Alhamdulillah bisa shalat berjamaah terus hari ini.',
                        'Tadarus terasa lancar, insya Allah besok tambah lembar.',
                        'Lupa dzikir pagi karena terburu-buru ke sekolah.',
                        'Alhamdulillah puasa sunnah senin lancar.',
                        'Semoga besok bisa tahajud lebih awal.',
                        'Target tadarus hari ini tercapai.',
                        'Ingin memperbaiki kekhusyukan shalat subuh.',
                    ];
                    $catatan = $reflections[array_rand($reflections)];
                }

                $verifiedBy = null;
                if ($parentId && rand(1, 100) <= 70) {
                    $verifiedBy = $parentId;
                }

                // Create log
                $log = MutabaahLog::create([
                    'student_id' => $student->id,
                    'mutabaah_item_id' => null, // detailed
                    'date' => $dateStr,
                    'sholat_subuh' => $subuh,
                    'sholat_dzuhur' => $dzuhur,
                    'sholat_ashar' => $ashar,
                    'sholat_maghrib' => $maghrib,
                    'sholat_isya' => $isya,
                    'sholat_dhuha' => $dhuha,
                    'sholat_tahajud' => $tahajud,
                    'tadarus_pages' => $tadarus,
                    'puasa_sunnah' => $puasa,
                    'sedekah' => $sedekah,
                    'dzikir_pagi' => $dzikirPagi,
                    'dzikir_sore' => $dzikirSore,
                    'catatan_harian' => $catatan,
                    'verified_by' => $verifiedBy,
                ]);
            }

            // Generate weekly summaries for the last 5 weeks
            for ($w = 5; $w >= 0; $w--) {
                $weekStart = now()->subWeeks($w)->startOfWeek()->format('Y-m-d');
                $mutabaahService->generateWeeklySummary($student->id, $weekStart);
            }
        }
    }
}
