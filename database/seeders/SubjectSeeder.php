<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            // Ciri Khas / Keislaman
            ['name' => 'PAI', 'code' => 'PAI', 'category' => 'Ciri Khas'],
            ['name' => 'BTAQ', 'code' => 'BTAQ', 'category' => 'Ciri Khas'],
            ['name' => 'Program Tahfidz', 'code' => 'THFDZ', 'category' => 'Ciri Khas'],
            ['name' => 'Bahasa Arab', 'code' => 'ARB', 'category' => 'Ciri Khas'],

            // Khusus / Layanan
            ['name' => 'Bimbingan Konseling', 'code' => 'BK', 'category' => 'Ciri Khas'],
            ['name' => 'PSB', 'code' => 'PSB', 'category' => 'Ciri Khas'], // Pusat Sumber Belajar / Penerimaan Santri Baru?

            // Muatan Lokal
            ['name' => 'Bahasa Jawa', 'code' => 'JWA', 'category' => 'Lokal'],

            // Nasional (Bahasa)
            ['name' => 'Bahasa Indonesia', 'code' => 'IND', 'category' => 'Nasional'],
            ['name' => 'BHS. IND Lanjut', 'code' => 'IND-L', 'category' => 'Nasional'],
            ['name' => 'Bahasa Inggris', 'code' => 'ING', 'category' => 'Nasional'],
            ['name' => 'Bahasa Inggris Lanjut', 'code' => 'ING-L', 'category' => 'Nasional'],

            // Nasional (MIPA & Teknologi)
            ['name' => 'Matematika', 'code' => 'MTK', 'category' => 'Nasional'],
            ['name' => 'Matematika Lanjut', 'code' => 'MTK-L', 'category' => 'Nasional'],
            ['name' => 'Fisika', 'code' => 'FSK', 'category' => 'Nasional'],
            ['name' => 'Kimia', 'code' => 'KMA', 'category' => 'Nasional'],
            ['name' => 'Biologi', 'code' => 'BIO', 'category' => 'Nasional'],
            ['name' => 'Informatika', 'code' => 'TIK', 'category' => 'Nasional'],

            // Nasional (IPS & Sosial)
            ['name' => 'Sosiologi', 'code' => 'SOS', 'category' => 'Nasional'],
            ['name' => 'Sejarah', 'code' => 'SJH', 'category' => 'Nasional'],
            ['name' => 'Geografi', 'code' => 'GEO', 'category' => 'Nasional'],
            ['name' => 'Ekonomi', 'code' => 'EKO', 'category' => 'Nasional'],

            // Nasional (Umum)
            ['name' => 'PEND. PANCASILA', 'code' => 'PKN', 'category' => 'Nasional'],
            ['name' => 'PJOK', 'code' => 'PJK', 'category' => 'Nasional'],
            ['name' => 'Seni Budaya', 'code' => 'SBD', 'category' => 'Nasional'],
            ['name' => 'PKWU', 'code' => 'KWU', 'category' => 'Nasional'],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['code' => $subject['code']],
                [
                    'name' => $subject['name'],
                    'category' => $subject['category'],
                ]
            );
        }
    }
}
