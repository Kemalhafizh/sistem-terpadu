<?php

namespace Database\Seeders;

use App\Models\CertificateTemplate;
use App\Models\IssuedCertificate;
use App\Models\StudentRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentExperienceSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::where('role', 'siswa')->first() ?? User::factory()->create(['role' => 'siswa', 'name' => 'Siswa Experience']);
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin', 'name' => 'Admin Experience']);

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

    }
}
