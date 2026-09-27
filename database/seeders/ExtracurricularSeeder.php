<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Extracurricular;
use App\Models\ExtracurricularAchievement;
use App\Models\ExtracurricularActivity;
use App\Models\ExtracurricularAttendance;
use App\Models\ExtracurricularGrade;
use App\Models\ExtracurricularMember;
use App\Models\Student;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ExtracurricularSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $academicYear = AcademicYear::current() ?: AcademicYear::first();

        // 1. Find teachers to act as advisors
        $teachers = User::where('role', 'guru')->pluck('id');
        if ($teachers->isEmpty()) {
            $teachers = collect([1]); // fallback
        }

        // 2. Create 7 clubs
        $clubsData = [
            ['name' => 'Pramuka SMA ISA 1', 'desc' => 'Gerakan kepanduan nasional pembentuk karakter dan kemandirian.', 'day' => 'Jumat', 'time' => '14:00 - 16:00', 'loc' => 'Lapangan Utama Sekolah'],
            ['name' => 'Paskibra (Pasukan Pengibar Bendera)', 'desc' => 'Pembinaan disiplin, ketahanan mental, dan baris-berbaris formal.', 'day' => 'Sabtu', 'time' => '08:00 - 11:00', 'loc' => 'Lapangan Utama Sekolah'],
            ['name' => 'Palang Merah Remaja (PMR)', 'desc' => 'Latihan pertolongan pertama, kesiapsiagaan bencana, dan kepedulian sosial.', 'day' => 'Kamis', 'time' => '15:30 - 17:00', 'loc' => 'Ruang UKS Sekolah'],
            ['name' => 'Klub Futsal ISA 1', 'desc' => 'Pengembangan bakat sepak bola mini dan kerja sama tim olahraga.', 'day' => 'Rabu', 'time' => '16:00 - 18:00', 'loc' => 'Lapangan Olahraga Indoor'],
            ['name' => 'Klub Basket ISA 1', 'desc' => 'Latihan teknik bola basket dan ketangkasan fisik berprestasi.', 'day' => 'Selasa', 'time' => '16:00 - 18:00', 'loc' => 'Lapangan Basket Sekolah'],
            ['name' => 'English Debate & Club', 'desc' => 'Mengasah kemampuan berbicara bahasa Inggris umum, debat, dan story telling.', 'day' => 'Rabu', 'time' => '15:30 - 17:00', 'loc' => 'Ruang Kelas XI-A'],
            ['name' => 'Klub Robotika & Coding', 'desc' => 'Eksplorasi teknologi mikrokontroler, pemrograman sensor, dan rakitan robot.', 'day' => 'Kamis', 'time' => '15:30 - 17:30', 'loc' => 'Laboratorium Komputer'],
        ];

        $clubs = collect();
        foreach ($clubsData as $c) {
            $club = Extracurricular::create([
                'name' => $c['name'],
                'description' => $c['desc'],
                'advisor_id' => $faker->randomElement($teachers),
                'schedule_day' => $c['day'],
                'schedule_time' => $c['time'],
                'location' => $c['loc'],
                'max_members' => 30,
                'is_active' => true,
            ]);
            $clubs->push($club);
        }

        // 3. Get students to enroll as members
        $students = Student::all();
        if ($students->isEmpty()) {
            return;
        }

        $memberCount = 0;
        foreach ($students as $student) {
            // Enroll in 1 or 2 clubs
            $assignedClubs = $faker->randomElements($clubs->all(), $faker->numberBetween(1, 2));
            foreach ($assignedClubs as $club) {
                $role = 'anggota';
                // Set first few as leaders
                if ($memberCount < $clubs->count()) {
                    $role = 'ketua';
                } elseif ($memberCount < $clubs->count() * 2) {
                    $role = 'sekretaris';
                }

                $member = ExtracurricularMember::create([
                    'extracurricular_id' => $club->id,
                    'student_id' => $student->id,
                    'role' => $role,
                    'status' => 'active',
                    'joined_at' => now()->subMonths(6),
                ]);

                // Create Semester Grade for this member
                ExtracurricularGrade::create([
                    'extracurricular_member_id' => $member->id,
                    'academic_year_id' => $academicYear->id,
                    'grade' => $faker->randomElement(['A', 'B', 'C']),
                    'description' => $faker->randomElement([
                        'Aktif mengikuti latihan dan menunjukkan penguasaan teknik dasar yang sangat baik.',
                        'Disiplin dalam kehadiran dan mampu bekerja sama dengan baik dalam tim.',
                        'Menunjukkan perkembangan yang cukup baik selama latihan semester ini.',
                    ]),
                ]);

                $memberCount++;
            }
        }

        // 4. Seed Activities and Attendances (30 total activities)
        foreach ($clubs as $club) {
            // Generate 5 activities per club
            for ($j = 0; $j < 5; $j++) {
                $activityDate = now()->subWeeks(5 - $j)->toDateString();
                $activity = ExtracurricularActivity::create([
                    'extracurricular_id' => $club->id,
                    'title' => 'Latihan Rutin Pertemuan ke-'.($j + 1),
                    'description' => $faker->sentence(12),
                    'activity_date' => $activityDate,
                    'gallery_paths' => null,
                    'created_by' => $club->advisor_id,
                ]);

                // Seed Attendances for this activity
                $members = $club->members;
                foreach ($members as $m) {
                    ExtracurricularAttendance::create([
                        'extracurricular_id' => $club->id,
                        'date' => $activityDate,
                        'student_id' => $m->student_id,
                        'status' => $faker->randomElement(['H', 'H', 'H', 'H', 'A', 'S', 'I']), // high weight on H
                        'notes' => $faker->randomElement([null, null, 'Izin sakit', 'Tanpa keterangan']),
                    ]);
                }
            }

            // 5. Seed Extracurricular Achievements (lomba/kompetisi)
            if ($faker->boolean(60)) {
                $level = $faker->randomElement(['provinsi', 'nasional', 'kota']);
                $winner = $faker->randomElement(['Juara 1', 'Juara 2', 'Juara 3', 'Harapan 1']);

                ExtracurricularAchievement::create([
                    'extracurricular_id' => $club->id,
                    'title' => 'Kompetisi Tahunan Cabang '.$club->name,
                    'level' => $level,
                    'event_date' => now()->subMonths($faker->numberBetween(1, 4))->toDateString(),
                    'organizer' => 'Dinas Kepemudaan dan Olahraga',
                    'participants' => [$faker->name, $faker->name, $faker->name],
                    'notes' => 'Meraih '.$winner.' setelah mengalahkan 20 peserta lainnya.',
                ]);
            }
        }
    }
}
