<?php

namespace Database\Seeders;

use App\Models\CertificateTemplate;
use App\Models\CourseEvaluation;
use App\Models\DigitalIdCard;
use App\Models\IssuedCertificate;
use App\Models\StudentRequest;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StudentExperienceSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::where('role', 'siswa')->first() ?? User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Experience']);
        $teacher = User::where('role', 'guru')->first() ?? User::factory()->create(['role' => 'guru', 'name' => 'Guru Experience']);
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin', 'name' => 'Admin Experience']);
        $subject = Subject::first() ?? Subject::create(['name' => 'Fisika Merdeka', 'code' => 'FIS-MDK']);

        // 40.1: Student Request
        StudentRequest::create([
            'student_id' => $student->id,
            'request_type' => 'transcript',
            'status' => 'completed',
            'details' => ['notes' => 'Perlu transkrip semester 1-5 untuk SNBP'],
            'processed_by' => $admin->id,
            'admin_notes' => 'Transkrip telah disahkan dan diserahkan.',
            'resolved_at' => now(),
        ]);

        // 40.2: Digital ID Card
        DigitalIdCard::create([
            'student_id' => $student->id,
            'card_hash' => Str::random(32),
            'issue_date' => now(),
            'expiry_date' => now()->addYears(3),
            'is_active' => true,
        ]);

        // 40.3: Certificate Template & Issued Certificate
        $tpl = CertificateTemplate::create([
            'name' => 'Sertifikat Kelulusan Tahfidz 30 Juz',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        IssuedCertificate::create([
            'student_id' => $student->id,
            'template_id' => $tpl->id,
            'verification_code' => 'CERT-ISA1-998',
            'metadata' => ['event' => 'Wisuda Tahfidz 2026'],
            'issued_at' => now(),
        ]);

        // 40.4: Course Evaluation
        CourseEvaluation::create([
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'academic_year' => '2025/2026',
            'semester' => 'Genap',
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(20),
            'is_published' => true,
        ]);
    }
}
