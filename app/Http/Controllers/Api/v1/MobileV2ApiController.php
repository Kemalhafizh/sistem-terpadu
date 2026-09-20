<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class MobileV2ApiController extends Controller
{
    use ApiResponseTrait;

    /**
     * GET /api/v1/home/contextual
     */
    public function getContextualHome(Request $request)
    {
        $user = $request->user();
        $hour = (int) date('H');
        $timeOfDay = match (true) {
            $hour < 11 => 'morning',
            $hour < 15 => 'afternoon',
            $hour < 18 => 'evening',
            default => 'night',
        };

        return $this->successResponse([
            'context' => [
                'timeOfDay' => $timeOfDay,
                'isExamSeason' => false,
                'activeEvent' => 'Pekan Penguatan Karakter & Tahfidz Terpadu',
                'hijriDate' => '14 Rabiul Awal 1448 H',
            ],
            'hero' => [
                'type' => 'academic_greeting',
                'title' => 'Ahlan wa Sahlan, ' . ($user ? $user->name : 'Siswa Sulaone'),
                'subtitle' => 'Tetap istiqomah dalam menuntut ilmu dan berakhlak mulia hari ini.',
                'gradientStart' => '#0D5C3A',
                'gradientEnd' => '#083823',
                'iconEmoji' => 'star',
                'actionLabel' => 'Cek Mutaba\'ah',
                'actionRoute' => 'mutabaah',
            ],
            'quickActions' => [
                ['id' => 'cbt', 'label' => 'Ujian CBT', 'iconUrl' => 'quiz', 'route' => 'cbt_list', 'priority' => 1],
                ['id' => 'schedule', 'label' => 'Jadwal', 'iconUrl' => 'schedule', 'route' => 'schedule', 'priority' => 2],
                ['id' => 'gamification', 'label' => 'Level & XP', 'iconUrl' => 'military_tech', 'route' => 'gamification_dashboard', 'priority' => 3],
                ['id' => 'tutor', 'label' => 'Sultan AI', 'iconUrl' => 'smart_toy', 'route' => 'ai_tutor', 'priority' => 4],
            ],
            'suggestions' => [
                [
                    'id' => 'sugg_1',
                    'type' => 'ibadah',
                    'message' => 'Sudahkah Anda melaksanakan salat Dhuha dan membaca Al-Ma\'tsurat pagi ini?',
                    'ctaText' => 'Isi Mutaba\'ah',
                    'ctaRoute' => 'mutabaah',
                ],
                [
                    'id' => 'sugg_2',
                    'type' => 'academic',
                    'message' => 'Ada kuis Matematika Tingkat Lanjut besok jam 07.30 WIB.',
                    'ctaText' => 'Buka Materi LMS',
                    'ctaRoute' => 'elearning_class_list',
                ],
            ],
            'gamification' => [
                'xpToday' => 85,
                'totalXp' => 1420,
                'level' => 7,
                'streakDays' => 14,
            ],
        ], 'Data Contextual Home berhasil dimuat');
    }

    /**
     * GET /api/v1/home/suggestions
     */
    public function getSmartSuggestions(Request $request)
    {
        return $this->successResponse([
            [
                'id' => 'sugg_1',
                'type' => 'ibadah',
                'message' => 'Sudahkah Anda melaksanakan salat Dhuha dan membaca Al-Ma\'tsurat pagi ini?',
                'ctaText' => 'Isi Mutaba\'ah',
                'ctaRoute' => 'mutabaah',
            ],
            [
                'id' => 'sugg_2',
                'type' => 'academic',
                'message' => 'Ada kuis Matematika Tingkat Lanjut besok jam 07.30 WIB.',
                'ctaText' => 'Buka Materi LMS',
                'ctaRoute' => 'elearning_class_list',
            ],
            [
                'id' => 'sugg_3',
                'type' => 'library',
                'message' => 'Buku "Tafsir Al-Azhar Jilid 1" yang Anda pinjam jatuh tempo dalam 2 hari.',
                'ctaText' => 'Lihat Pinjaman',
                'ctaRoute' => 'library_catalog',
            ]
        ], 'Smart Suggestions berhasil dimuat');
    }

    /**
     * POST /api/v1/home/suggestions/{id}/dismiss
     */
    public function dismissSuggestion(Request $request, $id)
    {
        return $this->successResponse(null, "Saran $id berhasil ditandai selesai/dikesampingkan");
    }

    /**
     * GET /api/v1/gamification/profile
     */
    public function getGamificationProfile(Request $request)
    {
        return $this->successResponse([
            'totalXp' => 1420,
            'level' => 7,
            'levelTitle' => 'Thalibul Ilmi Mujahid',
            'xpToNextLevel' => 180,
            'streakDays' => 14,
            'longestStreak' => 28,
            'badgesEarned' => 12,
            'monthlyRank' => 3,
        ], 'Profil Gamifikasi berhasil dimuat');
    }

    /**
     * GET /api/v1/gamification/leaderboard
     */
    public function getLeaderboard(Request $request)
    {
        return $this->successResponse([
            ['rank' => 1, 'name' => 'Fathur Rahman', 'className' => 'XII MIPA 1', 'xp' => 1950, 'isCurrentUser' => false],
            ['rank' => 2, 'name' => 'Nabila Syakieb', 'className' => 'XII MIPA 2', 'xp' => 1680, 'isCurrentUser' => false],
            ['rank' => 3, 'name' => 'Ahmad Kemal Hafizh', 'className' => 'XII MIPA 1', 'xp' => 1420, 'isCurrentUser' => true],
            ['rank' => 4, 'name' => 'Zahra Amalia', 'className' => 'XII MIPA 1', 'xp' => 1340, 'isCurrentUser' => false],
            ['rank' => 5, 'name' => 'Muhammad Bilal', 'className' => 'XII IPS 1', 'xp' => 1290, 'isCurrentUser' => false],
            ['rank' => 6, 'name' => 'Khadijah Putri', 'className' => 'XII MIPA 3', 'xp' => 1180, 'isCurrentUser' => false],
            ['rank' => 7, 'name' => 'Rizky Pratama', 'className' => 'XII IPS 2', 'xp' => 1050, 'isCurrentUser' => false],
        ], 'Leaderboard berhasil dimuat');
    }

    /**
     * GET /api/v1/gamification/badges
     */
    public function getBadges(Request $request)
    {
        $earned = [
            ['slug' => 'first_checkin', 'name' => 'Hadir Tepat Waktu', 'description' => 'Presensi sebelum bel berbunyi 5 hari beruntun', 'iconEmoji' => 'alarm', 'category' => 'attendance', 'xpReward' => 100, 'isEarned' => true],
            ['slug' => 'tahfidz_star', 'name' => 'Bintang Tahfidz', 'description' => 'Menyelesaikan setoran 1 juz Al-Qur\'an', 'iconEmoji' => 'book', 'category' => 'ibadah', 'xpReward' => 250, 'isEarned' => true],
            ['slug' => 'cbt_master', 'name' => 'Juara CBT', 'description' => 'Meraih nilai sempurna 100 pada ujian CBT', 'iconEmoji' => 'score', 'category' => 'academic', 'xpReward' => 200, 'isEarned' => true],
            ['slug' => 'streak_14', 'name' => 'Istiqomah 14 Hari', 'description' => 'Mengisi Mutaba\'ah tanpa jeda selama 14 hari', 'iconEmoji' => 'streak', 'category' => 'streak', 'xpReward' => 150, 'isEarned' => true],
        ];

        $locked = [
            ['slug' => 'cbt_speedster', 'name' => 'Kilat CBT', 'description' => 'Selesaikan ujian dalam separuh durasi dengan nilai > 90', 'iconEmoji' => 'speed', 'category' => 'academic', 'xpReward' => 300, 'isEarned' => false],
            ['slug' => 'library_scholar', 'name' => 'Kutu Buku Sultan Agung', 'description' => 'Membaca dan meminjam 15 buku dalam 1 semester', 'iconEmoji' => 'library', 'category' => 'library', 'xpReward' => 250, 'isEarned' => false],
            ['slug' => 'streak_30', 'name' => 'Legenda Istiqomah 30 Hari', 'description' => 'Streak ibadah mutaba\'ah berturut-turut sebulan penuh', 'iconEmoji' => 'crown', 'category' => 'streak', 'xpReward' => 500, 'isEarned' => false],
        ];

        return $this->successResponse([
            'earned' => $earned,
            'locked' => $locked,
        ], 'Koleksi Lencana berhasil dimuat');
    }

    /**
     * GET /api/v1/gamification/history
     */
    public function getXpHistory(Request $request)
    {
        return $this->successResponse([
            ['id' => 'xp_1', 'activity' => 'Presensi Tepat Waktu (GPS Kampus)', 'xpEarned' => 20, 'createdAt' => 'Hari ini, 06:45'],
            ['id' => 'xp_2', 'activity' => 'Mutaba\'ah Subuh Berjamaah di Masjid', 'xpEarned' => 30, 'createdAt' => 'Hari ini, 05:10'],
            ['id' => 'xp_3', 'activity' => 'Tugas Matematika Tingkat Lanjut Dikumpulkan', 'xpEarned' => 35, 'createdAt' => 'Kemarin, 14:20'],
            ['id' => 'xp_4', 'activity' => 'Setoran Audio Tahsin Surat Al-Mulk', 'xpEarned' => 50, 'createdAt' => 'Kemarin, 09:00'],
        ], 'Riwayat XP berhasil dimuat');
    }

    /**
     * GET /api/v1/gamification/streak
     */
    public function getStreakCalendar(Request $request)
    {
        return $this->successResponse([
            'currentStreak' => 14,
            'longestStreak' => 28,
            'activeDays' => [
                '2026-09-01', '2026-09-02', '2026-09-03', '2026-09-04', '2026-09-05',
                '2026-09-06', '2026-09-07', '2026-09-08', '2026-09-09', '2026-09-10',
                '2026-09-11', '2026-09-12', '2026-09-13'
            ]
        ], 'Kalender Streak berhasil dimuat');
    }

    /**
     * GET /api/v1/notifications/preferences
     */
    public function getNotificationPreferences(Request $request)
    {
        return $this->successResponse([
            'examNotifsEnabled' => true,
            'assignmentNotifsEnabled' => true,
            'infoNotifsEnabled' => true,
            'prayerNotifsEnabled' => true,
            'digestMode' => 'realtime', // realtime, morning, evening
        ], 'Preferensi Notifikasi berhasil dimuat');
    }

    /**
     * PUT /api/v1/notifications/preferences
     */
    public function updateNotificationPreferences(Request $request)
    {
        $validated = $request->validate([
            'examNotifsEnabled' => 'nullable|boolean',
            'assignmentNotifsEnabled' => 'nullable|boolean',
            'infoNotifsEnabled' => 'nullable|boolean',
            'prayerNotifsEnabled' => 'nullable|boolean',
            'digestMode' => 'nullable|string',
        ]);

        return $this->successResponse($validated, 'Preferensi notifikasi berhasil diperbarui');
    }

    /**
     * GET /api/v1/parent/feed
     */
    public function getChildActivityFeed(Request $request)
    {
        return $this->successResponse([
            [
                'id' => 'act_1',
                'timestamp' => time() * 1000 - 3600000,
                'title' => 'Presensi Masuk Kampus',
                'type' => 'attendance',
                'description' => 'Ahmad Kemal Hafizh telah tiba di Kampus SMA Islam Sultan Agung 1 Semarang pukul 06.45 WIB.',
            ],
            [
                'id' => 'act_2',
                'timestamp' => time() * 1000 - 7200000,
                'title' => 'Mutaba\'ah Subuh Tuntas',
                'type' => 'ibadah',
                'description' => 'Ananda menyelesaikan checklist salat Subuh berjamaah & tilawah 1 ruku\'.',
            ],
            [
                'id' => 'act_3',
                'timestamp' => time() * 1000 - 86400000,
                'title' => 'Hasil Ulangan Harian Fisika',
                'type' => 'grade',
                'description' => 'Nilai Fisika Modern diraih dengan skor 94 (Tuntas Melampaui KKTP).',
            ],
            [
                'id' => 'act_4',
                'timestamp' => time() * 1000 - 172800000,
                'title' => 'Peminjaman Buku E-Pustaka',
                'type' => 'library',
                'description' => 'Meminjam buku "Fisika Kuantum untuk SMA" - jatuh tempo 7 hari ke depan.',
            ]
        ], 'Live Feed Aktivitas Anak berhasil dimuat');
    }

    /**
     * GET /api/v1/parent/weekly-digest
     */
    public function getWeeklyDigest(Request $request)
    {
        return $this->successResponse([
            'weekStart' => (time() - 7 * 86400) * 1000,
            'attendancePercentage' => 100.0,
            'averageGrade' => 91.5,
            'ibadahScore' => 96,
            'notes' => 'Performa belajar dan kedisiplinan ananda pekan ini sangat prima dan melampaui target KKTP.'
        ], 'Weekly Digest berhasil dimuat');
    }

    /**
     * GET /api/v1/parent/comparison
     */
    public function getChildComparison(Request $request)
    {
        return $this->successResponse([
            ['subject' => 'Matematika TL', 'childScore' => 92.0, 'classAverage' => 84.5],
            ['subject' => 'Fisika', 'childScore' => 94.0, 'classAverage' => 81.0],
            ['subject' => 'PAI & Tahfidz', 'childScore' => 98.0, 'classAverage' => 90.2],
            ['subject' => 'Bahasa Inggris', 'childScore' => 89.0, 'classAverage' => 83.0],
            ['subject' => 'Biologi', 'childScore' => 91.0, 'classAverage' => 85.0],
        ], 'Perbandingan Nilai Anak vs Rata-rata Kelas berhasil dimuat');
    }

    /**
     * GET /api/v1/ai/tutor/suggestions
     */
    public function getAiTutorSuggestions(Request $request)
    {
        return $this->successResponse([
            'Jelaskan konsep Limit Trigonometri dengan cara mudah dipahami',
            'Bagaimana hukum bacaan Mad Lazim Mukhaffaf Kilmi?',
            'Bantu saya membuat kerangka esai tentang Peradaban Islam Andalusia',
            'Berikan contoh soal UTBK Penalaran Matematika dan pembahasannya'
        ], 'Rekomendasi prompt AI Tutor berhasil dimuat');
    }
}
