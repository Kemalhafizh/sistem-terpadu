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

        $guruPai = User::where('email', 'guru.pai@sultanagung1.sch.id')->first()->id;
        $guruKimia = User::where('email', 'guru.kimia@sultanagung1.sch.id')->first()->id;
        $guruBiologi = User::where('email', 'guru.biologi@sultanagung1.sch.id')->first()->id;
        $guruSejarah = User::where('email', 'guru.sejarah@sultanagung1.sch.id')->first()->id;
        $guruEkonomi = User::where('email', 'guru.ekonomi@sultanagung1.sch.id')->first()->id;
        $wakaKesiswaan = User::where('email', 'waka.kesiswaan@sultanagung1.sch.id')->first()->id;

        // Classrooms (6 classrooms, 20 students each)
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
            [$guruFisika, ['FIS']],
            [$guruBahasa, ['BIG', 'BIN']],
            [$guruBk, ['BK']],
            [$guruPai, ['PAI', 'AQH']],
            [$guruKimia, ['KIM']],
            [$guruBiologi, ['BIO']],
            [$guruSejarah, ['SEJ']],
            [$guruEkonomi, ['EKO']],
        ];
        foreach ($pivotData as [$userId, $codes]) {
            $subjectIds = Subject::whereIn('code', $codes)->pluck('id');
            User::find($userId)->subjects()->attach($subjectIds);
        }

        // Students (120 total, 20 per classroom)
        $allClassrooms = Classroom::all();
        $studentUsers = User::where('role', 'student')->orderBy('id')->get();
        $parentUsers = User::where('role', 'parent')->orderBy('id')->get();

        $studentNames = [
            'Aditya Pratama', 'Bayu Aji', 'Candra Wijaya', 'Deni Setiawan', 'Fajar Ramadhan',
            'Gilang Dirga', 'Hendra Wijaya', 'Indra Lesmana', 'Joko Susilo', 'Kurniawan Dwi',
            'Lutfi Hakim', 'Miftah Farid', 'Noval Ardiansyah', 'Oki Setiawan', 'Putra Perdana',
            'Rian Hidayat', 'Sandi Yudha', 'Taufik Hidayat', 'Wahyu Prasetyo', 'Yudi Hermawan',
            'Zulham Zamrun', 'Amiruddin', 'Bambang Pamungkas', 'Cecep Supriadi', 'Dedi Kusnandar',
            'Edi Rahmayadi', 'Ferry Rotinsulu', 'Gaston Castano', 'Hamka Hamzah', 'Irfan Bachdim',
            'Liestiadi', 'Maman Abdurrahman', 'Nova Arianto', 'Okto Maniani', 'Ponaryo Astaman',
            'Qischil Gandrum', 'Rahmat Darmawan', 'Syamsul Chaeruddin', 'Tantan', 'Uston Nawawi',
            'Victor Igbonefo', 'Wijay', 'Yustinus Pae', 'Zulkifli Syukur', 'Anindya Putri',
            'Bella Shofie', 'Chika Jessica', 'Dian Sastrowardoyo', 'Isyana Sarasvati', 'Gisella Anastasia',
            'Haruka Nakagawa', 'Inul Daratista', 'Julia Perez', 'Kartika Putri', 'Luna Maya',
            'Nikita Mirzani', 'Olla Ramlan', 'Prilly Latuconsina', 'Queenarsy', 'Raline Shah',
            'Sandra Dewi', 'Titiek Puspa', 'Ussy Sulistiawaty', 'Vicky Shu', 'Wulan Guritno',
            'Zaskia Adya Mecca', 'Angel Lelga', 'Bunga Citra Lestari', 'Cinta Laura', 'Dewi Perssik',
            'Evi Masamba', 'Fitri Carlina', 'Gracia Indri', 'Hetty Koes Endang', 'Iis Dahlia',
            'Jenita Janet', 'Kristina', 'Lesti Andryani', 'Meggy Z', 'Nassar',
            'Ovi Sovianti', 'Rossa', 'Siti Badriah', 'Trio Macan', 'Uut Permatasari',
            'Via Vallen', 'Wika Salim', 'Zaskia Gotik', 'Ade Rai', 'Chris John',
            'Taufik Hidayat', 'Liliyana Natsir', 'Hendra Setiawan', 'Markis Kido', 'Susi Susanti',
            'Alan Budikusuma', 'Triyaningsih', 'Eko Yuli Irawan', 'Sri Wahyuni', 'Lalu Muhammad Zohri',
        ];

        $counter = 0;
        foreach ($allClassrooms as $idx => $classroom) {
            for ($i = 0; $i < 20; $i++) {
                $studentIndex = $idx * 20 + $i;

                // First 25 students use user accounts from UserSeeder
                if ($studentIndex < count($studentUsers)) {
                    $userId = $studentUsers[$studentIndex]->id;
                    $name = $studentUsers[$studentIndex]->name;
                } else {
                    // Create user accounts dynamically for the remaining 95 students
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

                // First 25 students have parents from UserSeeder parentUsers list
                $parentId = null;
                if ($studentIndex < count($parentUsers)) {
                    $parentId = $parentUsers[$studentIndex]->id;
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
