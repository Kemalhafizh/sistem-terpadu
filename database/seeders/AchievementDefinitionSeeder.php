<?php
namespace Database\Seeders;

use App\Models\AchievementDefinition;
use Illuminate\Database\Seeder;

class AchievementDefinitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $achievements = [
            [
                'code' => 'first_day',
                'name' => 'Hari Pertama ',
                'description' => 'Masuk ke platform SISTA untuk pertama kali dan rasakan petualangan barumu.',
                'icon' => '',
                'color' => '#3b82f6', // Blue
                'category' => 'akademik',
                'rarity' => 'common',
                'xp_reward' => 5,
                'criteria' => ['type' => 'xp_milestone', 'value' => 5],
                'is_auto' => true,
                'is_active' => true,
            ],
            [
                'code' => 'perfect_week',
                'name' => 'Minggu Sempurna ⭐',
                'description' => 'Hadiri kegiatan belajar di kelas secara penuh selama 5 hari sekolah berturut-turut.',
                'icon' => '⭐',
                'color' => '#10b981', // Green
                'category' => 'kehadiran',
                'rarity' => 'common',
                'xp_reward' => 15,
                'criteria' => ['type' => 'attendance_streak', 'value' => 5],
                'is_auto' => true,
                'is_active' => true,
            ],
            [
                'code' => 'perfect_month',
                'name' => 'Bulan Sempurna ',
                'description' => 'Hadiri kegiatan belajar di kelas secara penuh selama 1 bulan penuh tanpa alpa.',
                'icon' => '',
                'color' => '#f59e0b', // Amber
                'category' => 'kehadiran',
                'rarity' => 'rare',
                'xp_reward' => 50,
                'criteria' => ['type' => 'attendance_streak', 'value' => 20],
                'is_auto' => true,
                'is_active' => true,
            ],
            [
                'code' => 'streak_30',
                'name' => 'Streak 30 Hari ',
                'description' => 'Pertahankan catatan presensi kehadiran masuk kelas selama 30 hari berturut-turut.',
                'icon' => '',
                'color' => '#ef4444', // Red
                'category' => 'kehadiran',
                'rarity' => 'epic',
                'xp_reward' => 100,
                'criteria' => ['type' => 'attendance_streak', 'value' => 30],
                'is_auto' => true,
                'is_active' => true,
            ],
            [
                'code' => 'top_scorer',
                'name' => 'Juara Kelas ',
                'description' => 'Raih pencapaian tinggi dan kumpulkan total minimal 1000 XP semester ini.',
                'icon' => '',
                'color' => '#fbbf24', // Amber glow
                'category' => 'akademik',
                'rarity' => 'rare',
                'xp_reward' => 75,
                'criteria' => ['type' => 'xp_milestone', 'value' => 1000],
                'is_auto' => true,
                'is_active' => true,
            ],
            [
                'code' => 'bookworm',
                'name' => 'Kutu Buku ',
                'description' => 'Pinjam buku perpustakaan atau kumpulkan total minimal 500 XP untuk kecintaan pada literasi.',
                'icon' => '',
                'color' => '#6366f1', // Indigo
                'category' => 'sosial',
                'rarity' => 'rare',
                'xp_reward' => 40,
                'criteria' => ['type' => 'xp_milestone', 'value' => 500],
                'is_auto' => true,
                'is_active' => true,
            ],
            [
                'code' => 'forum_helper',
                'name' => 'Penolong Forum ',
                'description' => 'Berpartisipasi aktif dengan memposting minimal 20 balasan di forum diskusi akademik.',
                'icon' => '',
                'color' => '#06b6d4', // Cyan
                'category' => 'sosial',
                'rarity' => 'rare',
                'xp_reward' => 60,
                'criteria' => ['type' => 'forum_replies', 'value' => 20],
                'is_auto' => true,
                'is_active' => true,
            ],
            [
                'code' => 'tahfidz_juz',
                'name' => 'Hafiz Juz Al-Quran ',
                'description' => 'Lakukan setoran hafalan Al-Quran sebanyak 10 kali di bawah bimbingan guru tahfidz.',
                'icon' => '',
                'color' => '#10b981', // Emerald green
                'category' => 'keislaman',
                'rarity' => 'legendary',
                'xp_reward' => 200,
                'criteria' => ['type' => 'tahfidz_setoran', 'value' => 10],
                'is_auto' => true,
                'is_active' => true,
            ],
            [
                'code' => 'mutabaah_streak',
                'name' => 'Istiqomah Ibadah ',
                'description' => 'Konsisten mengisi catatan amal ibadah harian mutaba\'ah yaumi sebanyak 30 hari.',
                'icon' => '',
                'color' => '#8b5cf6', // Purple
                'category' => 'keislaman',
                'rarity' => 'epic',
                'xp_reward' => 120,
                'criteria' => ['type' => 'mutabaah_streak', 'value' => 30],
                'is_auto' => true,
                'is_active' => true,
            ],
            [
                'code' => 'quiz_ace',
                'name' => 'Ace Ujian CBT ',
                'description' => 'Tunjukkan keunggulan akademik dengan mengumpulkan total minimal 1500 XP.',
                'icon' => '',
                'color' => '#ec4899', // Pink
                'category' => 'akademik',
                'rarity' => 'epic',
                'xp_reward' => 80,
                'criteria' => ['type' => 'xp_milestone', 'value' => 1500],
                'is_auto' => true,
                'is_active' => true,
            ],
            [
                'code' => 'early_bird',
                'name' => 'Siswa Terajin ',
                'description' => 'Raih minimal 800 XP sebagai bukti keaktifan menyelesaikan tugas kelas E-Learning awal waktu.',
                'icon' => '',
                'color' => '#14b8a6', // Teal
                'category' => 'akademik',
                'rarity' => 'common',
                'xp_reward' => 20,
                'criteria' => ['type' => 'xp_milestone', 'value' => 800],
                'is_auto' => true,
                'is_active' => true,
            ],
            [
                'code' => 'sholat_champion',
                'name' => 'Sholat Champion ',
                'description' => 'Jaga amalan ibadah sholat 5 waktu secara konsisten dan catat 40 kali mutaba\'ah harian.',
                'icon' => '',
                'color' => '#059669', // Dark emerald
                'category' => 'keislaman',
                'rarity' => 'legendary',
                'xp_reward' => 150,
                'criteria' => ['type' => 'mutabaah_streak', 'value' => 40],
                'is_auto' => true,
                'is_active' => true,
            ],
        ];

        foreach ($achievements as $ach) {
            AchievementDefinition::updateOrCreate(
                ['code' => $ach['code']],
                $ach
            );
        }
    }
}
