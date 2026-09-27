<?php

namespace App\Services;

use Carbon\Carbon;

class ParentExperienceService
{
    /**
     * Get real-time chronological activity feed of the child.
     */
    public function getChildActivityFeed(int $childId): array
    {
        return [
            [
                'id' => 'feed_1',
                'timestamp' => Carbon::today()->setTime(7, 10)->timestamp,
                'title' => 'Presensi Masuk Berhasil',
                'type' => 'attendance',
                'description' => 'Ananda hadir di Kampus SMA Islam Sultan Agung 1 via Geofence GPS tepat waktu.',
            ],
            [
                'id' => 'feed_2',
                'timestamp' => Carbon::today()->setTime(9, 45)->timestamp,
                'title' => 'Nilai Kuis Fisika Terbit',
                'type' => 'academic',
                'description' => 'Memperoleh nilai 92 pada materi Dinamika Rotasi Kurikulum Merdeka.',
            ],
            [
                'id' => 'feed_3',
                'timestamp' => Carbon::today()->setTime(12, 15)->timestamp,
                'title' => 'Shalat Dhuhur Berjamaah',
                'type' => 'ibadah',
                'description' => 'Tercatat mengikuti Shalat Dhuhur berjamaah di Masjid Sultan Agung.',
            ],
            [
                'id' => 'feed_4',
                'timestamp' => Carbon::yesterday()->setTime(14, 0)->timestamp,
                'title' => 'Peminjaman Buku Perpustakaan',
                'type' => 'library',
                'description' => 'Meminjam buku "Fisika Modern Edisi 4" dari E-Pustaka Pintar.',
            ],
        ];
    }

    /**
     * Generate weekly digest for parent.
     */
    public function generateWeeklyDigest(int $childId): array
    {
        return [
            'weekStart' => Carbon::now()->startOfWeek()->timestamp,
            'attendancePercentage' => 98.5,
            'averageGrade' => 89.2,
            'ibadahScore' => 95,
            'highlights' => [
                'Hadir 100% tepat waktu sepanjang pekan ini.',
                'Menyelesaikan 3 tugas LMS dengan predikat Sangat Baik.',
                'Istiqomah membaca Al-Ma\'tsurat dan tilawah 1 juz.',
            ],
        ];
    }

    /**
     * Get anonymous class comparison benchmark.
     */
    public function getChildVsClassAverage(int $childId): array
    {
        return [
            [
                'subject' => 'Matematika Tingkat Lanjut',
                'childScore' => 92.0,
                'classAverage' => 84.5,
            ],
            [
                'subject' => 'Fisika',
                'childScore' => 88.0,
                'classAverage' => 82.0,
            ],
            [
                'subject' => 'Pendidikan Agama Islam',
                'childScore' => 96.0,
                'classAverage' => 89.0,
            ],
            [
                'subject' => 'Bahasa Inggris',
                'childScore' => 90.0,
                'classAverage' => 85.0,
            ],
            [
                'subject' => 'Biologi',
                'childScore' => 87.0,
                'classAverage' => 83.5,
            ],
        ];
    }

    /**
     * Get parent quick contextual actions.
     */
    public function getParentQuickActions(int $parentId): array
    {
        return [
            [
                'id' => 'pay_spp',
                'title' => 'Bayar SPP',
                'subtitle' => 'Bulan September 2026',
                'route' => 'billing',
                'badge' => 'Jatuh Tempo',
            ],
            [
                'id' => 'chat_teacher',
                'title' => 'Chat Wali Kelas',
                'subtitle' => 'Konsultasi Belajar',
                'route' => 'conversation_list',
            ],
            [
                'id' => 'view_rapor',
                'title' => 'Rapor KKTP',
                'subtitle' => 'Capaian Pembelajaran',
                'route' => 'rapor_detail',
            ],
            [
                'id' => 'permit_request',
                'title' => 'Izin Sakit / Pulang',
                'subtitle' => 'Kirim Surat Dokter',
                'route' => 'attendance_permit',
            ],
        ];
    }
}
