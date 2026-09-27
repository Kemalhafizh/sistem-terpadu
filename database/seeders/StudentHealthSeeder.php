<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\HealthScreening;
use App\Models\HealthScreeningResult;
use App\Models\Student;
use App\Models\StudentHealthRecord;
use App\Models\UksMedicine;
use App\Models\UksVisit;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class StudentHealthSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $academicYear = AcademicYear::current() ?: AcademicYear::first();

        // 1. Seed UKS Medicines
        $medicinesData = [
            ['name' => 'Paracetamol 500mg', 'cat' => 'Obat Bebas', 'stock' => 120, 'min' => 20, 'exp' => now()->addYear()->toDateString()],
            ['name' => 'Ibuprofen 400mg', 'cat' => 'Obat Bebas Terbatas', 'stock' => 80, 'min' => 15, 'exp' => now()->addMonths(8)->toDateString()],
            ['name' => 'Antasida Doen', 'cat' => 'Obat Bebas', 'stock' => 150, 'min' => 30, 'exp' => now()->addMonths(10)->toDateString()],
            ['name' => 'Betadine Antiseptik 15ml', 'cat' => 'Obat Luar', 'stock' => 15, 'min' => 5, 'exp' => now()->addYears(2)->toDateString()],
            ['name' => 'Minyak Kayu Putih 120ml', 'cat' => 'Obat Luar', 'stock' => 12, 'min' => 3, 'exp' => now()->addYears(3)->toDateString()],
            ['name' => 'Diapet Tablet', 'cat' => 'Obat Bebas', 'stock' => 100, 'min' => 20, 'exp' => now()->addMonths(14)->toDateString()],
            ['name' => 'Oralit Sac', 'cat' => 'Obat Bebas', 'stock' => 200, 'min' => 50, 'exp' => now()->addMonths(18)->toDateString()],
            ['name' => 'Salonpas Koyo', 'cat' => 'Obat Luar', 'stock' => 250, 'min' => 40, 'exp' => now()->addMonths(6)->toDateString()],
            // Expiring soon to trigger alert
            ['name' => 'Promag Tablet (Batch A)', 'cat' => 'Obat Bebas', 'stock' => 30, 'min' => 10, 'exp' => now()->addDays(20)->toDateString()],
            // Low stock to trigger alert
            ['name' => 'Bodrex Flu & Batuk', 'cat' => 'Obat Bebas Terbatas', 'stock' => 2, 'min' => 10, 'exp' => now()->addMonths(12)->toDateString()],
        ];

        $medicines = collect();
        foreach ($medicinesData as $med) {
            $m = UksMedicine::create([
                'name' => $med['name'],
                'category' => $med['cat'],
                'stock' => $med['stock'],
                'min_stock' => $med['min'],
                'expiry_date' => $med['exp'],
                'notes' => 'Penyimpanan lemari A.',
            ]);
            $medicines->push($m);
        }

        // 2. Get 60 Students for health records
        $students = Student::take(60)->get();
        if ($students->isEmpty()) {
            return;
        }

        $bloodTypes = ['A', 'B', 'AB', 'O'];
        foreach ($students as $student) {
            StudentHealthRecord::create([
                'student_id' => $student->id,
                'blood_type' => $faker->randomElement($bloodTypes),
                'allergies' => $faker->boolean(20) ? [$faker->randomElement(['Debu', 'Udara Dingin', 'Kacang', 'Seafood'])] : null,
                'chronic_conditions' => $faker->boolean(10) ? [$faker->randomElement(['Asma Ringan', 'Gastritis (Maag)'])] : null,
                'medications' => null,
                'medical_notes' => $faker->boolean(15) ? 'Perlu pengawasan saat aktivitas fisik berat.' : null,
                'emergency_contact_name' => $faker->name,
                'emergency_contact_phone' => '08'.$faker->numerify('##########'),
                'insurance_number' => $faker->boolean(70) ? $faker->numerify('BPJS-000#########') : null,
                'height_cm' => $faker->randomFloat(1, 150, 180),
                'weight_kg' => $faker->randomFloat(1, 45, 80),
                'vision_status' => $faker->randomElement(['normal', 'minus', 'normal']),
            ]);
        }

        // 3. Create 1 Health Screening Event
        $screening = HealthScreening::create([
            'title' => 'Pemeriksaan Kesehatan Berkala Siswa 2026',
            'screening_date' => now()->subMonths(2)->toDateString(),
            'academic_year_id' => $academicYear->id,
            'target_classrooms' => ['X-A', 'X-B', 'XI-A'],
            'status' => 'completed',
            'notes' => 'Kerjasama dengan Puskesmas Genuk Semarang.',
        ]);

        // Health Screening Results for the 60 students
        foreach ($students as $student) {
            HealthScreeningResult::create([
                'screening_id' => $screening->id,
                'student_id' => $student->id,
                'height_cm' => $faker->randomFloat(1, 150, 180),
                'weight_kg' => $faker->randomFloat(1, 45, 80),
                'vision_left' => $faker->randomElement(['20/20', '20/30', '20/40']),
                'vision_right' => $faker->randomElement(['20/20', '20/30', '20/40']),
                'dental_status' => $faker->randomElement(['Sehat', 'Karies Ringan', 'Karang Gigi']),
                'findings' => $faker->boolean(20) ? 'Perlu pemeriksaan gigi lanjutan' : 'Sehat',
                'recommendations' => $faker->boolean(20) ? 'Rujuk ke dokter gigi Puskesmas' : 'Pertahankan pola hidup sehat',
            ]);
        }

        // 4. Create 30 UKS Visit Logs
        $uksStaff = User::where('email', 'uks@sultanagung1.sch.id')->first();
        $handledById = $uksStaff ? $uksStaff->id : 1;

        $complaints = [
            ['comp' => 'Pusing dan Mual', 'diag' => 'Kelelahan & Maag Kambuh', 'treat' => 'Rest & Antasida', 'sev' => 'sedang'],
            ['comp' => 'Luka Robek di Lutut', 'diag' => 'Luka Lecet akibat Terjatuh di Lapangan', 'treat' => 'Pembersihan luka & Betadine', 'sev' => 'ringan'],
            ['comp' => 'Badan Demam & Menggigil', 'diag' => 'Suspek Influenza', 'treat' => 'Kompres hangat & Paracetamol', 'sev' => 'berat'],
            ['comp' => 'Nyeri Haid Hebat', 'diag' => 'Dismenore Primer', 'treat' => 'Istirahat di UKS & Feminax', 'sev' => 'sedang'],
            ['comp' => 'Sesak Napas', 'diag' => 'Asma Kambuh akibat Debu', 'treat' => 'Oksigenasi & Inhaler', 'sev' => 'berat'],
        ];

        for ($i = 0; $i < 30; $i++) {
            $student = $faker->randomElement($students);
            $complaint = $faker->randomElement($complaints);

            $visit = UksVisit::create([
                'student_id' => $student->id,
                'handled_by' => $handledById,
                'visit_time' => now()->subDays(30 - $i)->hour($faker->numberBetween(8, 14))->minute($faker->numberBetween(0, 59)),
                'leave_time' => now()->subDays(30 - $i)->hour($faker->numberBetween(9, 15))->minute($faker->numberBetween(0, 59)),
                'complaint' => $complaint['comp'],
                'diagnosis' => $complaint['diag'],
                'treatment' => $complaint['treat'],
                'severity' => $complaint['sev'],
                'disposition' => $faker->randomElement(['istirahat_uks', 'kembali_kelas', 'rujuk_rs', 'pulang']),
                'parent_notified' => $complaint['sev'] === 'berat',
                'notes' => $faker->sentence(5),
            ]);

            // Attach medicine
            $med = $faker->randomElement($medicines);
            $visit->medicines()->attach($med->id, [
                'quantity' => $faker->numberBetween(1, 2),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Decrement medicine stock
            $med->decrement('stock', 1);
        }
    }
}
