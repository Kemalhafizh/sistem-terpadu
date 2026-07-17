<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\SpmbPeriod;
use App\Models\SpmbWave;
use App\Models\SpmbTrack;
use App\Models\Registration;
use App\Models\SpmbDocument;
use App\Models\User;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class PpdbSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $academicYear = AcademicYear::current() ?: AcademicYear::first();
        
        if (!$academicYear) {
            $academicYear = AcademicYear::create([
                'year' => '2025/2026',
                'semester' => 'Genap',
                'is_active' => true,
                'start_date' => '2026-01-06',
                'end_date' => '2026-06-21'
            ]);
        }

        // 1. SpmbPeriod
        $period = SpmbPeriod::create([
            'uuid' => (string) Str::uuid(),
            'academic_year_id' => $academicYear->id,
            'name' => 'SPMB ' . $academicYear->year,
            'total_quota' => 150,
            'status' => 'open'
        ]);

        // 2. SpmbWaves
        $wave1 = SpmbWave::create([
            'uuid' => (string) Str::uuid(),
            'spmb_period_id' => $period->id,
            'name' => 'Gelombang 1 (Reguler & Beasiswa)',
            'registration_start' => now()->subMonths(3)->toDateString(),
            'registration_end' => now()->subMonths(1)->toDateString(),
            'announcement_date' => now()->subDays(25)->toDateString(),
            'quota' => 60,
            'registration_fee' => 200000,
            'status' => 'closed'
        ]);

        $wave2 = SpmbWave::create([
            'uuid' => (string) Str::uuid(),
            'spmb_period_id' => $period->id,
            'name' => 'Gelombang 2 (Reguler & Kemitraan)',
            'registration_start' => now()->subDays(10)->toDateString(),
            'registration_end' => now()->addDays(20)->toDateString(),
            'announcement_date' => now()->addDays(25)->toDateString(),
            'quota' => 90,
            'registration_fee' => 250000,
            'status' => 'open'
        ]);

        // 3. SpmbTracks
        $trackReguler = SpmbTrack::create([
            'uuid' => (string) Str::uuid(),
            'spmb_period_id' => $period->id,
            'name' => 'Jalur Reguler',
            'quota' => 90,
            'scoring_weights' => ['rapor' => 40, 'tes' => 60],
            'requirements' => 'Rata-rata rapor minimal 75 dan lulus ujian masuk tertulis.'
        ]);

        $trackPrestasi = SpmbTrack::create([
            'uuid' => (string) Str::uuid(),
            'spmb_period_id' => $period->id,
            'name' => 'Jalur Prestasi',
            'quota' => 30,
            'scoring_weights' => ['rapor' => 30, 'tes' => 30, 'prestasi' => 40],
            'requirements' => 'Pernah menjuarai lomba minimal tingkat kota/kabupaten.'
        ]);

        $trackTahfidz = SpmbTrack::create([
            'uuid' => (string) Str::uuid(),
            'spmb_period_id' => $period->id,
            'name' => 'Jalur Tahfidz',
            'quota' => 30,
            'scoring_weights' => ['rapor' => 20, 'tes' => 20, 'tahfidz' => 60],
            'requirements' => 'Hafal Al-Quran minimal 3 Juz dibuktikan dengan sertifikat/syahadah.'
        ]);

        $tracks = [$trackReguler, $trackPrestasi, $trackTahfidz];
        $waves = [$wave1, $wave2];

        // 4. Seed Registrations (70 total)
        // Categories:
        // - 20 Pending/Draft: has_paid_registration = false, status = 'draft', document_status = 'incomplete'
        // - 15 Verified: has_paid_registration = true, status = 'verified', document_status = 'verified'
        // - 15 Test: has_paid_registration = true, status = 'test', document_status = 'verified', with test scores
        // - 10 Accepted: status = 'accepted', has_reregistered = false
        // - 10 Reregistered: status = 'accepted', has_reregistered = true

        $schools = ['SMP Negeri 1 Semarang', 'SMP Negeri 2 Semarang', 'SMP Islam Sultan Agung 1', 'SMP Islam Sultan Agung 3', 'MTs Negeri 1 Semarang', 'SMP Negeri 5 Semarang', 'SMP Negeri 9 Semarang'];

        // Category 1: 20 Pending/Draft
        for ($i = 0; $i < 20; $i++) {
            $this->createRegistration(
                $faker,
                $academicYear->id,
                $faker->randomElement($waves),
                $faker->randomElement($tracks),
                'draft',
                'incomplete',
                false,
                false,
                $faker->randomElement($schools)
            );
        }

        // Category 2: 15 Verified
        for ($i = 0; $i < 15; $i++) {
            $this->createRegistration(
                $faker,
                $academicYear->id,
                $faker->randomElement($waves),
                $faker->randomElement($tracks),
                'verified',
                'verified',
                true,
                false,
                $faker->randomElement($schools)
            );
        }

        // Category 3: 15 Test
        for ($i = 0; $i < 15; $i++) {
            $this->createRegistration(
                $faker,
                $academicYear->id,
                $faker->randomElement($waves),
                $faker->randomElement($tracks),
                'test',
                'verified',
                true,
                false,
                $faker->randomElement($schools),
                $faker->randomFloat(2, 70, 95)
            );
        }

        // Category 4: 10 Accepted
        for ($i = 0; $i < 10; $i++) {
            $this->createRegistration(
                $faker,
                $academicYear->id,
                $wave1, // Gel 1 usually accepted
                $faker->randomElement($tracks),
                'accepted',
                'verified',
                true,
                false,
                $faker->randomElement($schools),
                $faker->randomFloat(2, 80, 98)
            );
        }

        // Category 5: 10 Reregistered
        for ($i = 0; $i < 10; $i++) {
            $reg = $this->createRegistration(
                $faker,
                $academicYear->id,
                $wave1,
                $faker->randomElement($tracks),
                'accepted',
                'verified',
                true,
                true,
                $faker->randomElement($schools),
                $faker->randomFloat(2, 82, 99)
            );

            // Converted to Student
            $user = User::create([
                'name' => $reg->full_name,
                'email' => strtolower(str_replace(' ', '.', $reg->full_name)) . '@sista-student.com',
                'password' => bcrypt('password'),
                'role' => 'student',
                'phone_number' => $reg->parent_phone,
            ]);

            $reg->update(['user_id' => $user->id]);

            Student::create([
                'user_id' => $user->id,
                'classroom_id' => 1, // Default X-A
                'nis' => '10' . str_pad($reg->id, 4, '0', STR_PAD_LEFT),
                'nisn' => $reg->nisn,
                'gender' => $reg->gender,
                'birth_date' => $reg->birth_date,
                'address' => $reg->address,
                'status' => 'active',
            ]);
        }
    }

    private function createRegistration($faker, $academicYearId, $wave, $track, $status, $docStatus, $hasPaid, $hasReregistered, $school, $testScore = null)
    {
        $gender = $faker->randomElement(['L', 'P']);
        $firstName = $gender === 'L' ? $faker->firstNameMale : $faker->firstNameFemale;
        $lastName = $faker->lastName;
        $fullName = $firstName . ' ' . $lastName;

        $rapor = [
            'agama' => $faker->numberBetween(80, 95),
            'indonesia' => $faker->numberBetween(78, 95),
            'inggris' => $faker->numberBetween(75, 95),
            'matematika' => $faker->numberBetween(75, 95),
            'ipa' => $faker->numberBetween(78, 95),
            'ips' => $faker->numberBetween(80, 95),
        ];

        $tahfidz = [
            'juz_count' => $track->name === 'Jalur Tahfidz' ? $faker->numberBetween(3, 10) : $faker->numberBetween(0, 2),
        ];

        $totalScore = null;
        if ($testScore !== null) {
            $avgRapor = array_sum($rapor) / count($rapor);
            $totalScore = ($avgRapor * 0.4) + ($testScore * 0.6);
        }

        $reg = Registration::create([
            'registration_number' => Registration::generateRegistrationNumber(),
            'full_name' => $fullName,
            'email' => strtolower($firstName . '.' . $lastName . '@gmail.com'),
            'nisn' => $faker->numerify('##########'),
            'previous_school' => $school,
            'parent_phone' => '08' . $faker->numerify('##########'),
            'status' => $status,
            'academic_year_id' => $academicYearId,
            'spmb_wave_id' => $wave->id,
            'spmb_track_id' => $track->id,
            'gender' => $gender,
            'birth_date' => $faker->date('Y-m-d', '-15 years'),
            'birth_place' => $faker->city,
            'address' => $faker->address,
            'parent_name' => $faker->name,
            'rapor_scores' => $rapor,
            'achievement_data' => $track->name === 'Jalur Prestasi' ? [['title' => 'Juara 1 Lomba Pencak Silat', 'level' => 'Kota']] : null,
            'tahfidz_data' => $tahfidz,
            'entrance_test_score' => $testScore,
            'total_score' => $totalScore,
            'document_status' => $docStatus,
            'has_paid_registration' => $hasPaid,
            'has_reregistered' => $hasReregistered,
        ]);

        // Create Spmb Documents
        $docTypes = ['kk', 'certificate', 'akta', 'photo'];
        foreach ($docTypes as $type) {
            SpmbDocument::create([
                'registration_id' => $reg->id,
                'document_type' => $type,
                'file_path' => 'spmb/' . $reg->registration_number . '/' . $type . '.pdf',
                'verification_status' => $docStatus === 'verified' ? 'verified' : ($docStatus === 'incomplete' ? 'pending' : 'verified'),
                'verified_by' => $docStatus === 'verified' ? 1 : null,
                'verified_at' => $docStatus === 'verified' ? now() : null,
            ]);
        }

        return $reg;
    }
}
