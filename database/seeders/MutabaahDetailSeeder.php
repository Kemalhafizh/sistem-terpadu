<?php

namespace Database\Seeders;

use App\Models\MutabaahItem;
use App\Models\MutabaahLog;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class MutabaahDetailSeeder extends Seeder
{
    public function run(): void
    {
        // Mutabaah Items
        $items = [
            ['name' => 'Shalat Subuh Berjamaah', 'category' => 'wajib', 'points' => 5],
            ['name' => 'Shalat Dhuha', 'category' => 'sunnah', 'points' => 3],
            ['name' => 'Tilawah Al-Quran (2+ Halaman)', 'category' => 'wajib', 'points' => 5],
            ['name' => 'Puasa Sunnah', 'category' => 'sunnah', 'points' => 10],
            ['name' => 'Sedekah Harian', 'category' => 'akhlak', 'points' => 3],
            ['name' => 'Dzikir Pagi & Petang', 'category' => 'sunnah', 'points' => 2],
        ];

        MutabaahItem::truncate();
        MutabaahLog::truncate();

        $mutabaahItems = [];
        foreach ($items as $item) {
            $mutabaahItems[] = MutabaahItem::create([
                'name' => $item['name'],
                'category' => $item['category'],
            ]);
        }

        // Generate logs for first 30 students over the last 30 days
        $targetStudents = Student::take(30)->get();

        foreach ($targetStudents as $student) {
            $parentId = $student->parent_id;

            for ($d = 30; $d >= 0; $d--) {
                $date = now()->subDays($d)->format('Y-m-d');

                foreach ($mutabaahItems as $item) {
                    $isCompleted = rand(1, 100) <= 75; // 75% completion rate

                    // For sunnah/puasa, lower completion rate
                    if ($item->name === 'Puasa Sunnah') {
                        // Only likely on Monday (1) or Thursday (4)
                        $dayOfWeek = now()->subDays($d)->dayOfWeek;
                        if (in_array($dayOfWeek, [1, 4])) {
                            $isCompleted = rand(1, 100) <= 25; // 25% chance of fasting
                        } else {
                            $isCompleted = false;
                        }
                    }

                    $verifiedBy = null;
                    // If completed, 60% chance the parent has verified it
                    if ($isCompleted && $parentId && rand(1, 100) <= 60) {
                        $verifiedBy = $parentId;
                    }

                    MutabaahLog::create([
                        'student_id' => $student->id,
                        'mutabaah_item_id' => $item->id,
                        'is_completed' => $isCompleted,
                        'date' => $date,
                        'verified_by' => $verifiedBy,
                    ]);
                }
            }
        }
    }
}
