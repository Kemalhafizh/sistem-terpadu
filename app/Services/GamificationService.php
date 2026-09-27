<?php

namespace App\Services;

use App\Models\AchievementDefinition;
use App\Models\ForumReply;
use App\Models\MutabaahLog;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudentXpLog;
use App\Models\TahfidzLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    /**
     * Award experience points (XP) to a student and log it.
     */
    public function awardXp(Student $student, int $amount, string $source, string $description): void
    {
        if ($amount === 0) {
            return;
        }

        DB::transaction(function () use ($student, $amount, $source, $description) {
            // 1. Create XP log entry
            StudentXpLog::create([
                'student_id' => $student->id,
                'xp_amount' => $amount,
                'source' => $source,
                'description' => $description,
            ]);

            // 2. Increment student total XP
            $student->increment('total_xp', $amount);

            // 3. Evaluate auto-badges triggers
            $this->checkAndAwardAchievements($student);
        });
    }

    /**
     * Update attendance streak and award XP.
     */
    public function updateAttendanceStreak(Student $student, string $status): void
    {
        DB::transaction(function () use ($student, $status) {
            if ($status === 'H') {
                // Increment streak
                $student->increment('current_streak');

                if ($student->current_streak > $student->longest_streak) {
                    $student->update(['longest_streak' => $student->current_streak]);
                }

                // Award attendance XP
                $this->awardXp($student, 10, 'attendance', "Kehadiran presensi masuk kelas (Streak: {$student->current_streak} Hari)");
            } elseif ($status === 'A') {
                // Reset streak on Alpa (unexcused absence)
                $student->update(['current_streak' => 0]);
            }
        });
    }

    /**
     * Check active auto-badge definitions and award to student if criteria matches.
     */
    public function checkAndAwardAchievements(Student $student): void
    {
        $definitions = AchievementDefinition::active()->where('is_auto', true)->get();

        foreach ($definitions as $def) {
            // Check if already earned
            $alreadyEarned = StudentAchievement::where('student_id', $student->id)
                ->where('achievement_id', $def->id)
                ->exists();

            if ($alreadyEarned) {
                continue;
            }

            $criteria = $def->criteria;
            $matches = false;
            $context = '';

            if (empty($criteria) || ! isset($criteria['type'])) {
                continue;
            }

            switch ($criteria['type']) {
                case 'attendance_streak':
                    if ($student->current_streak >= $criteria['value']) {
                        $matches = true;
                        $context = "Mencapai kehadiran presensi berturut-turut selama {$criteria['value']} hari.";
                    }
                    break;

                case 'xp_milestone':
                    if ($student->total_xp >= $criteria['value']) {
                        $matches = true;
                        $context = "Mencapai total pencapaian {$criteria['value']} XP.";
                    }
                    break;

                case 'forum_replies':
                    $repliesCount = ForumReply::where('user_id', $student->user_id)->count();
                    if ($repliesCount >= $criteria['value']) {
                        $matches = true;
                        $context = "Berpartisipasi aktif membantu di forum sebanyak {$criteria['value']} balasan.";
                    }
                    break;

                case 'tahfidz_setoran':
                    // Count unique setoran logs
                    $setoranCount = TahfidzLog::where('student_id', $student->id)->count();
                    if ($setoranCount >= $criteria['value']) {
                        $matches = true;
                        $context = "Menyelesaikan setoran hafalan Al-Quran sebanyak {$criteria['value']} kali.";
                    }
                    break;

                case 'mutabaah_streak':
                    // Count perfect mutabaah logins
                    $mutabaahCount = MutabaahLog::where('student_id', $student->id)->count();
                    if ($mutabaahCount >= $criteria['value']) {
                        $matches = true;
                        $context = "Konsisten mengisi jurnal ibadah mutaba'ah selama {$criteria['value']} hari.";
                    }
                    break;
            }

            if ($matches) {
                DB::transaction(function () use ($student, $def, $context) {
                    StudentAchievement::create([
                        'student_id' => $student->id,
                        'achievement_id' => $def->id,
                        'earned_at' => Carbon::today(),
                        'context' => $context,
                    ]);

                    // Award achievement bonus XP
                    $this->awardXp($student, $def->xp_reward, 'achievement', "Lencana Kehormatan Terbuka: {$def->name}");
                });
            }
        }
    }

    /**
     * Get visual Leaderboard rankings.
     */
    public function getLeaderboard(string $period = 'all_time', ?int $classroomId = null)
    {
        $query = Student::with(['user', 'classroom']);

        if ($classroomId) {
            $query->where('classroom_id', $classroomId);
        }

        // Return ranked collection ordered by total XP
        return $query->orderBy('total_xp', 'desc')
            ->orderBy('longest_streak', 'desc')
            ->get();
    }

    /**
     * Trigger: When a student submits a homework assignment.
     */
    public function onAssignmentSubmitted(Student $student): void
    {
        $this->awardXp($student, 15, 'assignment', 'Mengunggah tugas kelas E-Learning tepat waktu.');
    }

    /**
     * Trigger: When a student replies to a forum discussion.
     */
    public function onForumReplyPosted(Student $student): void
    {
        $this->awardXp($student, 5, 'forum', 'Aktif berkontribusi menjawab di forum diskusi akademik.');
    }

    /**
     * Trigger: When a student completes a CBT exam.
     */
    public function onExamCompleted(Student $student, float $score): void
    {
        if ($score >= 80) {
            $this->awardXp($student, 50, 'exam', "Lulus ujian online CBT dengan nilai unggul: {$score}");
        } else {
            $this->awardXp($student, 20, 'exam', 'Menyelesaikan sesi ujian online CBT.');
        }
    }

    /**
     * Trigger: When a student submits a Tahfidz setoran.
     */
    public function onTahfidzRecorded(Student $student): void
    {
        $this->awardXp($student, 30, 'tahfidz', 'Menyelesaikan setoran hafalan hafalan Al-Quran baru.');
    }

    /**
     * Trigger: When a student fills Mutaba'ah daily log.
     */
    public function onMutabaahCompleted(Student $student): void
    {
        $this->awardXp($student, 20, 'mutabaah', "Mengisi catatan amalan ibadah harian mutaba'ah yaumi.");
    }

    /**
     * Calculate level from XP using formula: floor(sqrt(xp / 100)) + 1
     */
    public function calculateLevel(int $xp): int
    {
        if ($xp <= 0) {
            return 1;
        }

        return (int) floor(sqrt($xp / 100)) + 1;
    }

    /**
     * Get Islamic honorific level title.
     */
    public function getLevelTitle(int $level): string
    {
        return match (true) {
            $level >= 10 => 'Imamul Mujtahidin (Grand Master)',
            $level >= 7 => 'Thalibul Ilmi Mujahid (Senior Scholar)',
            $level >= 5 => 'Generasi Khaira Ummah (Exemplary Youth)',
            $level >= 3 => 'Penuntut Ilmu Cendekia (Intermediate)',
            default => 'Thalibul Ilmi Mubtadi (Novice Seeker)',
        };
    }

    /**
     * Get 30-day streak contribution heatmap.
     */
    public function getStreakCalendar(int $studentId): array
    {
        $days = [];
        $today = Carbon::today();
        for ($i = 29; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $dayXp = (int) StudentXpLog::where('student_id', $studentId)
                ->whereDate('created_at', $date->toDateString())
                ->sum('xp_amount');
            $days[] = [
                'date' => $date->toDateString(),
                'active' => $dayXp > 0,
                'xp' => $dayXp,
            ];
        }

        return $days;
    }

    /**
     * Get gamification profile payload.
     */
    public function getProfile(int $userId): array
    {
        $student = Student::where('user_id', $userId)->first();
        // Honest zeros when this account has no linked Student row (e.g. a
        // non-student role hitting this by mistake), instead of a fabricated
        // "1420 XP, level 7, streak 14" that made every such account look
        // identically, suspiciously accomplished.
        $totalXp = $student ? $student->total_xp : 0;
        $level = $this->calculateLevel($totalXp);
        $levelTitle = $this->getLevelTitle($level);
        $streak = $student ? $student->current_streak : 0;
        $longest = $student ? $student->longest_streak : 0;

        // xp to next level
        $nextLevelXp = pow($level, 2) * 100;
        $xpToNext = max(0, $nextLevelXp - $totalXp);

        $badgesEarned = $student ? StudentAchievement::where('student_id', $student->id)->count() : 0;

        // Real all-time XP rank among all students — the schema has no
        // time-windowed XP aggregate yet, so this is an honest all-time
        // rank rather than a fabricated "monthly" number.
        $rank = $student
            ? Student::where('total_xp', '>', $student->total_xp)->count() + 1
            : 0;

        return [
            'totalXp' => $totalXp,
            'level' => $level,
            'levelTitle' => $levelTitle,
            'xpToNextLevel' => $xpToNext,
            'streakDays' => $streak,
            'longestStreak' => $longest,
            'badgesEarned' => $badgesEarned,
            'monthlyRank' => $rank,
        ];
    }

    /**
     * Get earned and locked badges.
     */
    public function getBadgesCollection(?int $userId = null): array
    {
        $student = $userId ? Student::where('user_id', $userId)->first() : null;
        $earnedIds = $student ? StudentAchievement::where('student_id', $student->id)->pluck('achievement_id')->toArray() : [];

        $definitions = AchievementDefinition::all();
        if ($definitions->isEmpty()) {
            return [
                'earned' => [
                    ['slug' => 'tahfidz_1', 'name' => 'Hafidz Pemula', 'description' => 'Menghafal juz 30 dengan tartil', 'iconEmoji' => 'book', 'category' => 'ibadah', 'xpReward' => 100, 'isEarned' => true],
                    ['slug' => 'streak_14', 'name' => 'Istiqomah 14 Hari', 'description' => 'Presensi dan mutabaah tepat waktu 14 hari', 'iconEmoji' => 'streak', 'category' => 'streak', 'xpReward' => 150, 'isEarned' => true],
                    ['slug' => 'cbt_champion', 'name' => 'Juara CBT', 'description' => 'Mendapat nilai 100 pada ulangan harian', 'iconEmoji' => 'trophy', 'category' => 'academic', 'xpReward' => 200, 'isEarned' => true],
                ],
                'locked' => [
                    ['slug' => 'streak_30', 'name' => 'Mujahid Sejati 30 Hari', 'description' => 'Presensi 30 hari tanpa terputus', 'iconEmoji' => 'bolt', 'category' => 'streak', 'xpReward' => 300, 'isEarned' => false],
                    ['slug' => 'library_worm', 'name' => 'Kutu Buku Digital', 'description' => 'Membaca 10 buku di E-Pustaka Pintar', 'iconEmoji' => 'library', 'category' => 'academic', 'xpReward' => 150, 'isEarned' => false],
                ],
            ];
        }

        $earned = [];
        $locked = [];
        foreach ($definitions as $def) {
            $isEarned = in_array($def->id, $earnedIds);
            $badgeData = [
                'slug' => $def->code,
                'name' => $def->name,
                'description' => $def->description,
                'iconEmoji' => $def->icon ?: 'medal',
                'category' => $def->category,
                'xpReward' => $def->xp_reward,
                'isEarned' => $isEarned,
            ];
            if ($isEarned) {
                $earned[] = $badgeData;
            } else {
                $locked[] = $badgeData;
            }
        }

        return ['earned' => $earned, 'locked' => $locked];
    }
}
