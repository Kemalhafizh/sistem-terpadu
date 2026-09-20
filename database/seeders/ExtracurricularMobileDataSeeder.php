<?php

namespace Database\Seeders;

use App\Models\Extracurricular;
use App\Models\OsisAnnouncement;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExtracurricularMobileDataSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::where('role', 'guru')->first() ?: User::first();

        // 1. Seed Ekskul Realistis Sinkron dengan Android Mockup
        $ekskuls = [
            [
                'name' => 'Rohis Sultan Agung 1 (Karisma)',
                'category' => 'Keislaman & Da\'wah',
                'coach_name' => 'Ust. M. Rizqi, Lc., M.Hum',
                'schedule_day' => 'Jumat',
                'schedule_time' => '15:30 - 17:00',
                'location' => 'Masjid Sultan Agung Lt. 2',
                'description' => 'Pembinaan kepribadian islami, kajian tematik remaja, talaqqi Al-Qur\'an, dan manajemen kegiatan hari besar Islam.',
                'max_members' => 80,
                'is_active' => true,
                'is_registration_open' => true,
            ],
            [
                'name' => 'Klub Robotik & AI Sultan Agung',
                'category' => 'Sains & IT',
                'coach_name' => 'Ir. Tri Wahyudi, M.T',
                'schedule_day' => 'Rabu',
                'schedule_time' => '15:30 - 17:30',
                'location' => 'Lab IoT & Robotika Komputer',
                'description' => 'Pengembangan robot mikrokontroler Arduino, ESP32, Line Follower, dan Computer Vision untuk kompetisi nasional.',
                'max_members' => 40,
                'is_active' => true,
                'is_registration_open' => true,
            ],
            [
                'name' => 'Paskibraka SMA Islam Sultan Agung 1',
                'category' => 'Kepemimpinan & Baris Berbaris',
                'coach_name' => 'Pelatih Kodim 0733/BS',
                'schedule_day' => 'Selasa & Kamis',
                'schedule_time' => '16:00 - 17:30',
                'location' => 'Lapangan Upacara Utama',
                'description' => 'Pelatihan disiplin baris-berbaris, tata upacara bendera, dan seleksi Paskibraka Kota Semarang & Jawa Tengah.',
                'max_members' => 50,
                'is_active' => true,
                'is_registration_open' => true,
            ],
            [
                'name' => 'Basket Sula Putra & Putri',
                'category' => 'Olahraga Prestasi',
                'coach_name' => 'Coach Danny Hermawan',
                'schedule_day' => 'Senin & Sabtu',
                'schedule_time' => '16:00',
                'location' => 'Gelanggang Olahraga (GOR) Kampus',
                'description' => 'Latihan fisik intensif, taktik tanding, dan persiapan turnamen DBL Jawa Tengah Series.',
                'max_members' => 45,
                'is_active' => true,
                'is_registration_open' => true,
            ],
        ];

        foreach ($ekskuls as $item) {
            Extracurricular::updateOrCreate(
                ['name' => $item['name']],
                array_merge($item, ['advisor_id' => $teacher->id])
            );
        }

        // 2. Seed Post OSIS Realistis Sinkron dengan Android Mockup
        $osisPosts = [
            [
                'title' => 'Pembukaan Pendaftaran Sultan Agung Cup & Class Meeting 2026',
                'author' => 'Divisi Olahraga & Seni OSIS',
                'category' => 'Class Meeting',
                'content' => 'Siapkan tim terbaik kelasmu untuk cabang Futsal Antarkelas, E-Sport Mobile Legends, Cerdas Cermat Islami, dan Debat Bahasa Inggris! Hadiah total jutaan rupiah & Piala Bergilir Kepala Sekolah.',
                'likes_count' => 245,
                'is_published' => true,
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Pengumuman Bakal Calon Ketua & Wakil Ketua OSIS Periode 2026/2027',
                'author' => 'Komisi Pemilihan OSIS (KPO)',
                'category' => 'Pemilu OSIS',
                'content' => 'Debat Terbuka Visi & Misi Paslon akan diselenggarakan pada hari Kamis di Auditorium Utama. Seluruh siswa wajib memberikan hak suara secara digital melalui menu E-Voting SuperApp.',
                'likes_count' => 189,
                'is_published' => true,
                'published_at' => now()->subDays(5),
            ],
        ];

        foreach ($osisPosts as $post) {
            OsisAnnouncement::updateOrCreate(
                ['title' => $post['title']],
                $post
            );
        }
    }
}
