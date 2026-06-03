<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudentXpLog;
use App\Models\AchievementDefinition;
use App\Models\ForumReply;
use App\Models\ElearningSubmission;
use App\Models\TahfidzLog;
use App\Models\MutabaahLog;
use App\Models\ExamAttempt;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    /**
     * Award experience points (XP) to a student and log it.
     */
    public function awardXp(Student $student, int $amount, string $source, string $description): void
    {
        if ($amount === 0) return;

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

            if ($alreadyEarned) continue;

            $criteria = $def->criteria;
            $matches = false;
            $context = '';

            if (empty($criteria) || !isset($criteria['type'])) continue;

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
        $this->awardXp($student, 15, 'assignment', "Mengunggah tugas kelas E-Learning tepat waktu.");
    }

    /**
     * Trigger: When a student replies to a forum discussion.
     */
    public function onForumReplyPosted(Student $student): void
    {
        $this->awardXp($student, 5, 'forum', "Aktif berkontribusi menjawab di forum diskusi akademik.");
    }

    /**
     * Trigger: When a student completes a CBT exam.
     */
    public function onExamCompleted(Student $student, float $score): void
    {
        if ($score >= 80) {
            $this->awardXp($student, 50, 'exam', "Lulus ujian online CBT dengan nilai unggul: {$score}");
        } else {
            $this->awardXp($student, 20, 'exam', "Menyelesaikan sesi ujian online CBT.");
        }
    }

    /**
     * Trigger: When a student submits a Tahfidz setoran.
     */
    public function onTahfidzRecorded(Student $student): void
    {
        $this->awardXp($student, 30, 'tahfidz', "Menyelesaikan setoran hafalan hafalan Al-Quran baru.");
    }

    /**
     * Trigger: When a student fills Mutaba'ah daily log.
     */
    public function onMutabaahCompleted(Student $student): void
    {
        $this->awardXp($student, 20, 'mutabaah', "Mengisi catatan amalan ibadah harian mutaba'ah yaumi.");
    }
}
