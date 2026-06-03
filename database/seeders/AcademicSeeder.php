<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        // Academic Years
        AcademicYear::create(['year' => '2025/2026', 'semester' => 'Ganjil', 'is_active' => false, 'start_date' => '2025-07-14', 'end_date' => '2025-12-20']);
        $activeYear = AcademicYear::create(['year' => '2025/2026', 'semester' => 'Genap', 'is_active' => true, 'start_date' => '2026-01-06', 'end_date' => '2026-06-21']);

        // Guru IDs
        $guruTahfidz = User::where('email', 'guru.tahfidz@sultanagung1.sch.id')->first()->id;
        $guruMtk = User::where('email', 'guru.matematika@sultanagung1.sch.id')->first()->id;
        $guruBk = User::where('email', 'guru.bk@sultanagung1.sch.id')->first()->id;
        $guruFisika = User::where('email', 'guru.fisika@sultanagung1.sch.id')->first()->id;
        $guruBahasa = User::where('email', 'guru.bahasa@sultanagung1.sch.id')->first()->id;
        $wakaKesiswaan = User::where('email', 'waka.kesiswaan@sultanagung1.sch.id')->first()->id;

        // Classrooms
        $classrooms = [
            ['name' => 'X-A', 'grade_level' => 'X', 'major' => 'IPA', 'teacher_id' => $guruMtk],
            ['name' => 'X-B', 'grade_level' => 'X', 'major' => 'IPS', 'teacher_id' => $guruBahasa],
            ['name' => 'XI-A', 'grade_level' => 'XI', 'major' => 'IPA', 'teacher_id' => $guruFisika],
            ['name' => 'XI-B', 'grade_level' => 'XI', 'major' => 'IPS', 'teacher_id' => $guruBk],
            ['name' => 'XII-A', 'grade_level' => 'XII', 'major' => 'IPA', 'teacher_id' => $guruTahfidz],
            ['name' => 'XII-B', 'grade_level' => 'XII', 'major' => 'IPS', 'teacher_id' => $wakaKesiswaan],
        ];
        foreach ($classrooms as $c) {
            Classroom::create(array_merge($c, ['academic_year' => '2025/2026']));
        }

        // Subjects
        $subjects = [
            ['name' => 'Matematika', 'code' => 'MTK', 'category' => 'Nasional', 'kkm' => 75],
            ['name' => 'Fisika', 'code' => 'FIS', 'category' => 'Nasional', 'kkm' => 75],
            ['name' => 'Kimia', 'code' => 'KIM', 'category' => 'Nasional', 'kkm' => 75],
            ['name' => 'Biologi', 'code' => 'BIO', 'category' => 'Nasional', 'kkm' => 75],
            ['name' => 'Bahasa Inggris', 'code' => 'BIG', 'category' => 'Nasional', 'kkm' => 75],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIN', 'category' => 'Nasional', 'kkm' => 75],
            ['name' => 'Sejarah', 'code' => 'SEJ', 'category' => 'Nasional', 'kkm' => 75],
            ['name' => 'Ekonomi', 'code' => 'EKO', 'category' => 'Nasional', 'kkm' => 75],
            ['name' => 'Pendidikan Agama Islam', 'code' => 'PAI', 'category' => 'Ciri Khas', 'kkm' => 78],
            ['name' => 'Tahfidz Al-Quran', 'code' => 'THF', 'category' => 'Ciri Khas', 'kkm' => 80],
            ['name' => 'Al-Quran Hadits', 'code' => 'AQH', 'category' => 'Ciri Khas', 'kkm' => 78],
            ['name' => 'Bimbingan Konseling', 'code' => 'BK', 'category' => 'Lokal', 'kkm' => 0],
        ];
        foreach ($subjects as $s) {
            Subject::create($s);
        }

        // Pivot: subject_user (Guru → Mapel)
        $pivotData = [
            [$guruTahfidz, ['PAI', 'THF', 'AQH']],
            [$guruMtk, ['MTK']],
            [$guruFisika, ['FIS', 'KIM']],
            [$guruBahasa, ['BIG', 'BIN']],
            [$guruBk, ['BK']],
        ];
        foreach ($pivotData as [$userId, $codes]) {
            $subjectIds = Subject::whereIn('code', $codes)->pluck('id');
            User::find($userId)->subjects()->attach($subjectIds);
        }

        // Students (36 total, 6 per classroom)
        $allClassrooms = Classroom::all();
        $studentUsers = User::where('role', 'student')->get();
        $parentUsers = User::where('role', 'parent')->get();

        $studentNames = [
            'Abdullah Faqih', 'Bilqis Salsabila', 'Chairul Anwar', 'Dina Mariana',
            'Erlangga Putra', 'Fathia Zahra', 'Ghifari Akbar', 'Halimah Tusadiah',
            'Ibrahim Hasan', 'Jasmine Putri', 'Khalid Saifullah', 'Laila Nur Azizah',
            'Muhamad Raffi', 'Nadira Khairunnisa', 'Omar Farouq', 'Putri Rahmawati',
            'Qais Firdaus', 'Rania Saffanah', 'Salman Al-Farisi', 'Tasya Kamila',
            'Umar Hadi', 'Vera Indriani', 'Wahyu Ramadhan', 'Xena Maharani',
            'Yusuf Habibi', 'Zahra Aulia', 'Arif Rahman', 'Bella Safitri',
            'Dimas Aditya', 'Elsa Fadhilah',
        ];

        $counter = 0;
        foreach ($allClassrooms as $idx => $classroom) {
            for ($i = 0; $i < 6; $i++) {
                $studentIndex = $idx * 6 + $i;

                // First 6 students are linked to student user accounts
                if ($studentIndex < 6) {
                    $userId = $studentUsers[$studentIndex]->id;
                    $name = $studentUsers[$studentIndex]->name;
                } else {
                    // Create user accounts for remaining students
                    $name = $studentNames[$counter] ?? 'Siswa Demo '.($studentIndex + 1);
                    $counter++;
                    $newUser = User::create([
                        'name' => $name,
                        'email' => 'demo.siswa'.($studentIndex + 1).'@student.sa1.sch.id',
                        'password' => bcrypt('password123'),
                        'role' => 'student',
                        'email_verified_at' => now(),
                    ]);
                    $userId = $newUser->id;
                }

                $parentId = null;
                if ($studentIndex === 0) {
                    $parentId = $parentUsers[0]->id;
                }
                if ($studentIndex === 1) {
                    $parentId = $parentUsers[1]->id;
                }

                Student::create([
                    'user_id' => $userId,
                    'classroom_id' => $classroom->id,
                    'parent_id' => $parentId,
                    'nis' => 'SA1-'.str_pad($studentIndex + 1, 4, '0', STR_PAD_LEFT),
                    'nisn' => '30'.str_pad(mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                    'gender' => $i % 2 === 0 ? 'L' : 'P',
                    'birth_date' => now()->subYears(rand(15, 18))->subDays(rand(0, 365))->format('Y-m-d'),
                    'address' => 'Jl. '.['Siliwangi', 'Diponegoro', 'Imam Bonjol', 'Pandanaran', 'Gajah Mada', 'Pemuda'][rand(0, 5)].' No. '.rand(1, 200).', Semarang',
                    'status' => 'active',
                ]);
            }
        }
    }
}
