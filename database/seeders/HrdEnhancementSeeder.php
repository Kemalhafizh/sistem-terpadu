<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeTraining;
use App\Models\PerformanceEvaluation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class HrdEnhancementSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // 1. Get existing staff users
        $staffRoles = ['guru', 'bk', 'waka_kesiswaan', 'waka_kurikulum', 'waka_sarpras', 'waka_humas', 'staf_tu'];
        $users = User::whereIn('role', $staffRoles)->get();

        if ($users->isEmpty()) {
            // Seed a few dummy teachers if none exist (should not happen since UserSeeder runs first)
            $users = collect([
                User::create([
                    'name' => 'Dr. H. Ahmad Fauzi, M.Pd.',
                    'email' => 'ahmad.fauzi@sultanagung1.sch.id',
                    'password' => bcrypt('password'),
                    'role' => 'guru',
                    'phone_number' => '081223344556'
                ])
            ]);
        }

        $employees = collect();

        // 2. Create Employee records
        foreach ($users as $user) {
            $employee = Employee::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_number' => 'NIY.' . $faker->unique()->numerify('198######202#####'),
                    'position' => $this->getPositionByRole($user->role),
                    'employment_status' => $faker->randomElement(['gty', 'gtt', 'staf_tetap', 'honorer']),
                    'hire_date' => $faker->date('Y-m-d', '-5 years'),
                    'base_salary' => $faker->randomFloat(2, 4000000, 8000000),
                    'hourly_rate' => $faker->randomFloat(2, 50000, 100000),
                    'bank_name' => $faker->randomElement(['Bank Syariah Indonesia (BSI)', 'Bank Jateng Syariah', 'BNI Syariah']),
                    'bank_account' => $faker->bankAccountNumber,
                ]
            );
            $employees->push($employee);
        }

        // 3. Create 10 Performance Evaluations
        $evaluators = User::whereIn('role', ['kepala_sekolah', 'admin', 'superadmin'])->pluck('id');
        if ($evaluators->isEmpty()) {
            $evaluators = collect([1]); // Fallback to user ID 1
        }

        for ($i = 0; $i < 10; $i++) {
            $emp = $faker->randomElement($employees);
            $criteria = [
                'pedagogik' => $faker->numberBetween(70, 95),
                'profesional' => $faker->numberBetween(72, 98),
                'sosial' => $faker->numberBetween(75, 95),
                'kepribadian' => $faker->numberBetween(80, 98)
            ];
            $avg = array_sum($criteria) / count($criteria);
            
            $grade = 'B';
            if ($avg >= 90) $grade = 'A';
            elseif ($avg >= 80) $grade = 'B';
            elseif ($avg >= 70) $grade = 'C';
            elseif ($avg >= 60) $grade = 'D';
            else $grade = 'E';

            PerformanceEvaluation::create([
                'employee_id' => $emp->id,
                'evaluator_id' => $faker->randomElement($evaluators),
                'period' => 'Semester Genap 2025/2026',
                'criteria_scores' => $criteria,
                'total_score' => $avg,
                'grade' => $grade,
                'strengths' => $faker->sentence(10),
                'improvements' => $faker->sentence(8),
                'goals' => $faker->sentence(12),
                'status' => $faker->randomElement(['submitted', 'acknowledged']),
            ]);
        }

        // 4. Create 30 Employee Trainings
        $trainingTitles = [
            'Pelatihan Implementasi Kurikulum Merdeka Fase F',
            'Sertifikasi Pendidik Profesional Bidang Sains',
            'Workshop Pemanfaatan Artificial Intelligence (AI) dalam Pembelajaran',
            'Seminar Nasional Peningkatan Karakter Keislaman Siswa',
            'Diklat Kepemimpinan Guru Sekolah Menengah',
            'Webinar Konseling Kritis & Deteksi Dini Depresi Remaja',
            'Workshop Penulisan Jurnal Ilmiah Internasional Guru',
            'Pelatihan Pemanfaatan LMS Google Classroom & SISTA',
            'Diklat Pengurus UKS & Penanganan Cedera Olahraga',
            'Sertifikasi Kompetensi Pengelola Laboratorium Komputer'
        ];

        for ($i = 0; $i < 30; $i++) {
            $emp = $faker->randomElement($employees);
            $title = $faker->randomElement($trainingTitles);
            
            EmployeeTraining::create([
                'employee_id' => $emp->id,
                'title' => $title,
                'type' => $faker->randomElement(['workshop', 'seminar', 'sertifikasi', 'diklat', 'webinar']),
                'organizer' => $faker->randomElement(['Kemendikbudristek', 'YBWSA Semarang', 'Dinas Pendidikan Jawa Tengah', 'Lembaga Penjaminan Mutu Pendidikan (LPMP)']),
                'start_date' => $faker->date('Y-m-d', '-1 year'),
                'end_date' => $faker->date('Y-m-d', 'now'),
                'hours' => $faker->randomElement([24, 32, 48, 64]),
                'certificate_path' => 'trainings/cert_' . $faker->unique()->numerify('#####') . '.pdf',
                'certificate_number' => 'CERT/' . $faker->unique()->numerify('####/SISTA/#####'),
                'certificate_expiry' => $faker->randomElement([null, $faker->date('Y-m-d', '+2 years')]),
                'notes' => $faker->sentence(6),
            ]);
        }

        // 5. Create 50 Employee Documents (with 5 expiring soon to trigger alerts)
        $docTypes = [
            'sk' => ['SK Pengangkatan Yayasan', 'SK Wiyata Bakti', 'SK Pembagian Tugas Mengajar'],
            'kontrak' => ['Kontrak Kerja Tahunan', 'Surat Perjanjian Kerja'],
            'sertifikasi' => ['Sertifikat Pendidik Guru Madya', 'Sertifikat Keahlian Laboran'],
            'ijazah' => ['Ijazah Sarjana S1', 'Ijazah Magister S2'],
            'ktp' => ['KTP Elektronik Pegawai'],
            'npwp' => ['Kartu NPWP Pegawai'],
            'bpjs' => ['Kartu BPJS Kesehatan', 'Kartu BPJS Ketenagakerjaan']
        ];

        // 45 normal documents
        for ($i = 0; $i < 45; $i++) {
            $emp = $faker->randomElement($employees);
            $type = $faker->randomElement(array_keys($docTypes));
            $title = $faker->randomElement($docTypes[$type]);

            EmployeeDocument::create([
                'employee_id' => $emp->id,
                'title' => $title . ' - ' . $emp->name,
                'type' => $type,
                'file_path' => 'documents/emp_' . $emp->id . '/' . $type . '_' . $faker->unique()->numerify('#####') . '.pdf',
                'issue_date' => $faker->date('Y-m-d', '-3 years'),
                'expiry_date' => $faker->randomElement([null, $faker->date('Y-m-d', '+4 years')]),
                'notes' => $faker->sentence(4),
            ]);
        }

        // 5 expiring documents (expires in 10-30 days to trigger smart alerts)
        for ($i = 0; $i < 5; $i++) {
            $emp = $employees[$i % $employees->count()];
            $type = 'kontrak';
            $title = 'Surat Kontrak Kerja Tahunan Pegawai';

            EmployeeDocument::create([
                'employee_id' => $emp->id,
                'title' => $title . ' - ' . $emp->name,
                'type' => $type,
                'file_path' => 'documents/emp_' . $emp->id . '/contract_expiring.pdf',
                'issue_date' => now()->subDays(340)->toDateString(),
                'expiry_date' => now()->addDays($faker->numberBetween(10, 30))->toDateString(),
                'notes' => 'Kontrak kerja mendekati masa kedaluwarsa berkala.',
            ]);
        }
    }

    private function getPositionByRole(string $role): string
    {
        switch ($role) {
            case 'guru': return 'Guru Pengajar';
            case 'bk': return 'Guru Bimbingan Konseling';
            case 'waka_kesiswaan': return 'Wakil Kepala Sekolah Bidang Kesiswaan';
            case 'waka_kurikulum': return 'Wakil Kepala Sekolah Bidang Kurikulum';
            case 'waka_sarpras': return 'Wakil Kepala Sekolah Bidang Sarana & Prasarana';
            case 'waka_humas': return 'Wakil Kepala Sekolah Bidang Hubungan Masyarakat';
            case 'staf_tu': return 'Staf Tata Usaha';
            default: return 'Staf Sekolah';
        }
    }
}
