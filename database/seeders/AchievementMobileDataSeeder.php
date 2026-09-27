<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\BlockchainCredential;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class AchievementMobileDataSeeder extends Seeder
{
    public function run(): void
    {
        $student = Student::first();
        if (! $student) {
            $studentUser = User::where('role', 'student')->first();
            if ($studentUser) {
                $student = Student::create([
                    'user_id' => $studentUser->id,
                    'nis' => '110001',
                    'nisn' => '0061234567',
                ]);
            }
        }

        $wakaUser = User::whereIn('role', ['guru', 'admin'])->first();

        if ($student) {
            // 1. Prestasi Realistis Sinkron dengan Android Mockup
            $achievements = [
                [
                    'title' => 'Juara 1 Olimpiade Sains Nasional (OSN) Bidang Fisika Tingkat Provinsi Jawa Tengah',
                    'field' => 'Matematika & Sains',
                    'level' => 'PROVINSI',
                    'organizer' => 'Balai Pengembangan Talenta Indonesia (BPTI) Kemendikbud',
                    'event_date' => '2026-08-15',
                    'verification_status' => 'VALIDATED',
                    'points_earned' => 25,
                    'verified_by' => $wakaUser?->id,
                    'verified_at' => now()->subDays(30),
                    'certificate_path' => 'achievements/sample_osn.pdf',
                ],
                [
                    'title' => 'Juara 2 Musabaqah Tilawatil Qur\'an (MTQ) Tingkat Kota Semarang',
                    'field' => 'Keagamaan & Tahfidz',
                    'level' => 'KOTA',
                    'organizer' => 'Kementerian Agama & LPTQ Kota Semarang',
                    'event_date' => '2026-07-10',
                    'verification_status' => 'VALIDATED',
                    'points_earned' => 15,
                    'verified_by' => $wakaUser?->id,
                    'verified_at' => now()->subDays(65),
                    'certificate_path' => 'achievements/sample_mtq.pdf',
                ],
                [
                    'title' => 'Juara 1 Lomba Karya Ilmiah Remaja (LKIR) Nasional',
                    'field' => 'Matematika & Sains',
                    'level' => 'NASIONAL',
                    'organizer' => 'Badan Riset dan Inovasi Nasional (BRIN)',
                    'event_date' => '2026-06-28',
                    'verification_status' => 'PENDING_REVIEW',
                    'points_earned' => 50,
                    'certificate_path' => 'achievements/sample_lkir.pdf',
                ],
                [
                    'title' => 'Medali Emas Kejuaraan Pencak Silat Pelajar Se-Jawa Tengah',
                    'field' => 'Olahraga',
                    'level' => 'PROVINSI',
                    'organizer' => 'IPSI Jawa Tengah',
                    'event_date' => '2026-05-12',
                    'verification_status' => 'VALIDATED',
                    'points_earned' => 25,
                    'verified_by' => $wakaUser?->id,
                    'verified_at' => now()->subDays(120),
                    'certificate_path' => 'achievements/sample_silat.pdf',
                ],
            ];

            foreach ($achievements as $ach) {
                Achievement::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'title' => $ach['title'],
                    ],
                    $ach
                );
            }

            // 2. Sertifikat Digital Blockchain Sinkron dengan Android Mockup
            $certs = [
                [
                    'credential_type' => 'Sertifikat Kepanitiaan Sultan Agung Fest 2026',
                    'certificate_number' => 'CERT/SA1/2026/089',
                    'recipient_name' => $student->user?->name ?: 'Ahmad Kemal Hafiz',
                    'transaction_hash' => '0x7f8a9b2c3d4e5f6a1b2c3d4e5f6a7b8c9d0e1f2a',
                    'metadata_json' => [
                        'title' => 'Sertifikat Kepanitiaan Sultan Agung Fest 2026',
                        'role' => 'Koordinator Divisi IT & Publikasi',
                    ],
                    'issued_at' => '2026-08-20',
                ],
                [
                    'credential_type' => 'Sertifikat Peserta Terbaik LDK OSIS',
                    'certificate_number' => 'CERT/SA1/2026/042',
                    'recipient_name' => $student->user?->name ?: 'Ahmad Kemal Hafiz',
                    'transaction_hash' => '0x3e4f5a6b7c8d9e0f1a2b3c4d5e6f7a8b9c0d1e2f',
                    'metadata_json' => [
                        'title' => 'Sertifikat Peserta Terbaik LDK OSIS',
                        'role' => 'Peserta Terbaik Jenjang Kelas XI',
                    ],
                    'issued_at' => '2026-07-18',
                ],
            ];

            foreach ($certs as $c) {
                BlockchainCredential::updateOrCreate(
                    [
                        'certificate_number' => $c['certificate_number'],
                    ],
                    array_merge($c, [
                        'student_id' => $student->user_id,
                        'merkle_root' => '0x'.hash('sha256', $c['certificate_number']),
                        'contract_address' => '0x1234567890abcdef1234567890abcdef12345678',
                        'block_number' => 128456,
                    ])
                );
            }
        }
    }
}
