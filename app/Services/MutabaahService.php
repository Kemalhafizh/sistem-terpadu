<?php

namespace App\Services;

use App\Models\MutabaahLog;
use App\Models\MutabaahWeeklySummary;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MutabaahService
{
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

        return $summary;
    }
}
