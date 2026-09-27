<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserGamification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ContextualHomeService
{
    /**
     * Master aggregator for Home Screen payload.
     */
    public function getHomePayload(User $user): array
    {
        $now = Carbon::now('Asia/Jakarta');
        $hour = $now->hour;

        $timeOfDay = match (true) {
            $hour < 11 => 'morning',
            $hour < 15 => 'afternoon',
            $hour < 18 => 'evening',
            default => 'night',
        };

        $context = [
            'timeOfDay' => $timeOfDay,
            'isExamSeason' => false,
            'activeEvent' => 'Pekan Penguatan Karakter & Tahfidz Terpadu',
            'hijriDate' => '14 Rabiul Awal 1448 H',
        ];

        $hero = $this->resolveHeroCard($user, $timeOfDay);
        $quickActions = $this->getPrioritizedActions($user, $timeOfDay);
        $suggestions = $this->getSmartSuggestions($user);
        $gamification = $this->getGamificationSummary($user);

        return [
            'context' => $context,
            'hero' => $hero,
            'quickActions' => $quickActions,
            'suggestions' => $suggestions,
            'gamification' => $gamification,
        ];
    }

    /**
     * Resolve hero card based on user, time, and active alerts.
     */
    protected function resolveHeroCard(User $user, string $timeOfDay): array
    {
        $greeting = match ($timeOfDay) {
            'morning' => "Shabahul Khair, {$user->name}!",
            'afternoon' => "Ahlan wa Sahlan, {$user->name}!",
            'evening' => "Masa'ul Khair, {$user->name}!",
            default => "Selamat Beristirahat, {$user->name}!",
        };

        return [
            'type' => 'academic_greeting',
            'title' => $greeting,
            'subtitle' => 'Tetap istiqomah dalam menuntut ilmu dan berakhlak mulia di SMA Islam Sultan Agung 1.',
            'gradientStart' => '#0D5C3A',
            'gradientEnd' => '#063823',
            'iconEmoji' => 'star',
            'actionLabel' => 'Cek Mutaba\'ah',
            'actionRoute' => 'mutabaah',
        ];
    }

    /**
     * Get prioritized quick actions depending on role and context.
     */
    public function getPrioritizedActions(User $user, string $timeOfDay = 'morning'): array
    {
        $role = $user->role ?? 'student';

        if (str_contains(strtolower($role), 'parent') || str_contains(strtolower($role), 'ortu')) {
            return [
                ['id' => 'feed', 'label' => 'Aktivitas Anak', 'iconUrl' => 'child_care', 'route' => 'child_activity_feed', 'priority' => 1],
                ['id' => 'billing', 'label' => 'Tagihan SPP', 'iconUrl' => 'account_balance_wallet', 'route' => 'billing', 'priority' => 2],
                ['id' => 'report', 'label' => 'Rapor KKTP', 'iconUrl' => 'auto_graph', 'route' => 'rapor_detail', 'priority' => 3],
                ['id' => 'chat', 'label' => 'Konsultasi Guru', 'iconUrl' => 'chat', 'route' => 'conversation_list', 'priority' => 4],
            ];
        }

        if (str_contains(strtolower($role), 'teacher') || str_contains(strtolower($role), 'guru')) {
            return [
                ['id' => 'journal', 'label' => 'Jurnal KBM', 'iconUrl' => 'edit_note', 'route' => 'teaching_journal_mobile', 'priority' => 1],
                ['id' => 'attendance', 'label' => 'Presensi Kelas', 'iconUrl' => 'how_to_reg', 'route' => 'teacher_dashboard', 'priority' => 2],
                ['id' => 'assessment', 'label' => 'Penilaian PH', 'iconUrl' => 'fact_check', 'route' => 'daily_assessment_list', 'priority' => 3],
                ['id' => 'cbt', 'label' => 'Pengawas CBT', 'iconUrl' => 'quiz', 'route' => 'cbt_list', 'priority' => 4],
            ];
        }

        // Student default
        return [
            ['id' => 'cbt', 'label' => 'Ujian CBT', 'iconUrl' => 'quiz', 'route' => 'cbt_list', 'priority' => 1],
            ['id' => 'schedule', 'label' => 'Jadwal', 'iconUrl' => 'schedule', 'route' => 'schedule', 'priority' => 2],
            ['id' => 'gamification', 'label' => 'Level & XP', 'iconUrl' => 'military_tech', 'route' => 'gamification_dashboard', 'priority' => 3],
            ['id' => 'tutor', 'label' => 'Sultan AI', 'iconUrl' => 'smart_toy', 'route' => 'ai_tutor', 'priority' => 4],
        ];
    }

    /**
     * Get Smart Suggestions for user.
     */
    public function getSmartSuggestions(User $user): array
    {
        $dismissed = Cache::get("user_{$user->id}_dismissed_suggestions", []);

        $pool = [
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
            ],
        ];

        return array_values(array_filter($pool, fn ($s) => ! in_array($s['id'], $dismissed)));
    }

    /**
     * Dismiss a suggestion for the user.
     */
    public function dismissSuggestion(int $userId, string $suggestionId): void
    {
        $dismissed = Cache::get("user_{$userId}_dismissed_suggestions", []);
        if (! in_array($suggestionId, $dismissed)) {
            $dismissed[] = $suggestionId;
            Cache::put("user_{$userId}_dismissed_suggestions", $dismissed, 86400);
        }
    }

    /**
     * Get summary of gamification for home widget.
     */
    protected function getGamificationSummary(User $user): array
    {
        $gamification = UserGamification::where('user_id', $user->id)->first();
        if ($gamification) {
            return [
                'xpToday' => 85,
                'totalXp' => $gamification->total_xp,
                'level' => $gamification->level,
                'streakDays' => $gamification->current_streak_days,
            ];
        }

        return [
            'xpToday' => 85,
            'totalXp' => 1420,
            'level' => 7,
            'streakDays' => 14,
        ];
    }
}
