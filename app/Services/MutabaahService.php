<?php

namespace App\Services;

use App\Models\MutabaahLog;
use App\Models\MutabaahWeeklySummary;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MutabaahService
{
    protected $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    /**
     * Submit or update a daily detailed mutabaah log for a student.
     */
    public function submitDailyLog(Student $student, array $data, string $date): MutabaahLog
    {
        return DB::transaction(function () use ($student, $data, $date) {
            $log = MutabaahLog::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'mutabaah_item_id' => null, // null means detailed daily log
                    'date' => $date,
                ],
                [
                    'sholat_subuh' => (int) ($data['sholat_subuh'] ?? 0),
                    'sholat_dzuhur' => (int) ($data['sholat_dzuhur'] ?? 0),
                    'sholat_ashar' => (int) ($data['sholat_ashar'] ?? 0),
                    'sholat_maghrib' => (int) ($data['sholat_maghrib'] ?? 0),
                    'sholat_isya' => (int) ($data['sholat_isya'] ?? 0),
                    'sholat_dhuha' => (int) ($data['sholat_dhuha'] ?? 0),
                    'sholat_tahajud' => (int) ($data['sholat_tahajud'] ?? 0),
                    'tadarus_pages' => (int) ($data['tadarus_pages'] ?? 0),
                    'puasa_sunnah' => (bool) ($data['puasa_sunnah'] ?? false),
                    'sedekah' => (bool) ($data['sedekah'] ?? false),
                    'dzikir_pagi' => (bool) ($data['dzikir_pagi'] ?? false),
                    'dzikir_sore' => (bool) ($data['dzikir_sore'] ?? false),
                    'catatan_harian' => $data['catatan_harian'] ?? null,
                ]
            );

            // Trigger Gamification XP award based on daily progress
            $this->awardDailyXp($student, $log);

            // Re-calculate the weekly summary containing this date
            $carbonDate = Carbon::parse($date);
            $weekStart = $carbonDate->copy()->startOfWeek()->format('Y-m-d');
            $this->generateWeeklySummary($student->id, $weekStart);

            return $log;
        });
    }

    /**
     * Generate or update weekly summary for a student.
     */
    public function generateWeeklySummary(int $studentId, string $weekStart): MutabaahWeeklySummary
    {
        $startDate = Carbon::parse($weekStart);
        $endDate = $startDate->copy()->endOfWeek();

        $logs = MutabaahLog::where('student_id', $studentId)
            ->whereNull('mutabaah_item_id')
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        $loggedDaysCount = $logs->count();

        // Totals
        $avgScore = $loggedDaysCount > 0 ? (int) round($logs->avg('score')) : 0;
        
        $sholat5WaktuCount = 0;
        $sholatBerjamaahCount = 0;
        $tadarusTotalPages = 0;

        $sholats = ['sholat_subuh', 'sholat_dzuhur', 'sholat_ashar', 'sholat_maghrib', 'sholat_isya'];

        foreach ($logs as $log) {
            // Check if 5 wajib sholats are all completed (munfarid or jamaah)
            $completedWajib = true;
            foreach ($sholats as $sholat) {
                if ($log->{$sholat} < 1) {
                    $completedWajib = false;
                }
                if ($log->{$sholat} === 2) {
                    $sholatBerjamaahCount++;
                }
            }
            if ($completedWajib) {
                $sholat5WaktuCount++;
            }

            $tadarusTotalPages += $log->tadarus_pages;
        }

        // Determine grade based on average score
        $grade = 'D';
        if ($avgScore >= 85) {
            $grade = 'A';
        } elseif ($avgScore >= 70) {
            $grade = 'B';
        } elseif ($avgScore >= 55) {
            $grade = 'C';
        }

        $summary = MutabaahWeeklySummary::updateOrCreate(
            [
                'student_id' => $studentId,
                'week_start' => $startDate->format('Y-m-d'),
            ],
            [
                'week_end' => $endDate->format('Y-m-d'),
                'total_score' => $avgScore,
                'sholat_5_waktu_count' => $sholat5WaktuCount,
                'sholat_berjamaah_count' => $sholatBerjamaahCount,
                'tadarus_total_pages' => $tadarusTotalPages,
                'grade' => $grade,
            ]
        );

        // Award Gamification for high performance (weekly grade A)
        if ($grade === 'A') {
            $student = Student::find($studentId);
            if ($student) {
                // Streak checks and award bonus
                $this->gamificationService->awardXp($student, 30, 'mutabaah_streak', "Istiqomah Mingguan: Pencapaian Mutaba'ah Rata-rata Grade A.");
            }
        }

        return $summary;
    }

    /**
     * Calculate daily XP reward and trigger awards.
     */
    protected function awardDailyXp(Student $student, MutabaahLog $log): void
    {
        // 1. Base log completion
        $this->gamificationService->onMutabaahCompleted($student);

        // 2. Extra amalan XP
        $extraXp = 0;
        $xpReasons = [];

        // - Sholat 5 waktu lengkap -> +5 XP
        $sholats = ['sholat_subuh', 'sholat_dzuhur', 'sholat_ashar', 'sholat_maghrib', 'sholat_isya'];
        $sholatLengkap = true;
        $jamaahCount = 0;
        foreach ($sholats as $sholat) {
            if ($log->{$sholat} < 1) {
                $sholatLengkap = false;
            }
            if ($log->{$sholat} === 2) {
                $jamaahCount++;
            }
        }
        if ($sholatLengkap) {
            $extraXp += 5;
            $xpReasons[] = "Shalat 5 waktu lengkap (+5 XP)";
        }

        // - Sholat berjamaah -> +2 XP per sholat
        if ($jamaahCount > 0) {
            $extraXp += ($jamaahCount * 2);
            $xpReasons[] = "{$jamaahCount} Shalat Berjamaah (+".($jamaahCount * 2)." XP)";
        }

        // - Sholat dhuha -> +3 XP
        if ($log->sholat_dhuha >= 1) {
            $extraXp += 3;
            $xpReasons[] = "Shalat Dhuha (+3 XP)";
        }

        // - Tadarus >= 2 halaman -> +5 XP
        if ($log->tadarus_pages >= 2) {
            $extraXp += 5;
            $xpReasons[] = "Tadarus {$log->tadarus_pages} halaman (+5 XP)";
        }

        // - Puasa sunnah -> +10 XP
        if ($log->puasa_sunnah) {
            $extraXp += 10;
            $xpReasons[] = "Puasa Sunnah (+10 XP)";
        }

        // - Sedekah -> +3 XP (or +5 XP as in plan, let's use +5)
        if ($log->sedekah) {
            $extraXp += 5;
            $xpReasons[] = "Sedekah harian (+5 XP)";
        }

        // - Score 100 -> +20 XP
        if ($log->score === 100) {
            $extraXp += 20;
            $xpReasons[] = "Amalan Harian Sempurna Score 100 (+20 XP)";
        }

        if ($extraXp > 0) {
            $description = implode(', ', $xpReasons);
            $this->gamificationService->awardXp($student, $extraXp, 'mutabaah', "Bonus amalan mutaba'ah: {$description}");
        }
    }
}
