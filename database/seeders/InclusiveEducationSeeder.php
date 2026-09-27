<?php

namespace Database\Seeders;

use App\Models\AccommodationRegistry;
use App\Models\DifferentiatedPlan;
use App\Models\Employee;
use App\Models\IepDocument;
use App\Models\ScreeningResult;
use App\Models\Student;
use Illuminate\Database\Seeder;

class InclusiveEducationSeeder extends Seeder
{
    public function run(): void
    {
        $student = Student::first() ?? Student::factory()->create();
        $employee = Employee::first() ?? Employee::factory()->create();

        IepDocument::create([
            'student_id' => $student->id,
            'case_manager_id' => $employee->id,
            'start_date' => now(),
            'review_date' => now()->addYear(),
            'diagnoses' => ['Disleksia'],
            'present_level_of_performance' => 'Siswa menunjukkan kesulitan dalam membaca teks panjang namun unggul dalam tugas visual dan lisan.',
            'annual_goals' => [
                ['description' => 'Meningkatkan kecepatan membaca', 'progress' => '0%'],
            ],
            'required_accommodations' => ['Waktu ujian tambahan', 'Materi dalam format audio'],
            'status' => 'active',
        ]);

        ScreeningResult::create([
            'student_id' => $student->id,
            'evaluator_id' => $employee->id,
            'screening_type' => 'Awal Tahun Ajaran',
            'responses' => [
                ['question' => 'Fokus di kelas', 'value' => 8],
                ['question' => 'Interaksi sosial', 'value' => 5],
            ],
            'total_score' => 13,
            'risk_level' => 'moderate',
            'system_recommendation' => 'Pemantauan berkala dan penyesuaian metode pembelajaran di kelas.',
            'requires_professional_referral' => false,
        ]);

        DifferentiatedPlan::create([
            'differentiation_type' => 'process',
            'tier_1_strategy' => ['Instruksi visual', 'Pengulangan instruksi lisan'],
            'tier_2_strategy' => ['Bimbingan kelompok kecil', 'Penyederhanaan tugas'],
            'tier_3_strategy' => ['Bimbingan individual', 'Penugasan alternatif'],
        ]);

        AccommodationRegistry::create([
            'student_id' => $student->id,
            'accommodation_category' => 'exam',
            'specific_need' => 'Tambahan waktu 30 menit',
            'applicable_contexts' => ['Ujian Tengah Semester', 'Ujian Akhir Semester'],
            'provision_date' => now(),
            'notes' => 'Diperlukan pengawasan tambahan',
        ]);
    }
}
