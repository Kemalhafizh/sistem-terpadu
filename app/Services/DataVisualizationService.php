<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DataVisualizationService
{
    /**
     * Get 60-day attendance heatmap data.
     */
    public function getAttendanceHeatmap(): array
    {
        return Cache::remember('viz_attendance_heatmap', 300, function () {
            $heatmap = [];
            $today = Carbon::today();

            for ($i = 59; $i >= 0; $i--) {
                $date = $today->copy()->subDays($i);
                $dayOfWeek = $date->dayOfWeek;

                // Sunday or Saturday low attendance / weekend
                if ($dayOfWeek == 0 || $dayOfWeek == 6) {
                    $attendanceRate = 0.0;
                    $level = 0;
                } else {
                    // Simulated high attendance rate (94% - 99%)
                    $attendanceRate = 0.94 + (($date->day % 6) * 0.01);
                    $level = match (true) {
                        $attendanceRate >= 0.98 => 4,
                        $attendanceRate >= 0.96 => 3,
                        $attendanceRate >= 0.93 => 2,
                        default => 1,
                    };
                }

                $heatmap[$date->toDateString()] = [
                    'date' => $date->toDateString(),
                    'rate' => round($attendanceRate * 100, 1),
                    'level' => $level,
                ];
            }

            return $heatmap;
        });
    }

    /**
     * Get statistical grade distribution for exam (Mean, Median, StdDev).
     */
    public function getGradeDistribution(int $examId): array
    {
        $scores = [65, 70, 72, 75, 78, 80, 82, 85, 85, 88, 90, 92, 95, 96, 98, 100];

        $count = count($scores);
        $mean = array_sum($scores) / $count;

        // Median
        sort($scores);
        $mid = (int) floor($count / 2);
        $median = ($count % 2 == 0) ? ($scores[$mid - 1] + $scores[$mid]) / 2 : $scores[$mid];

        // Standard Deviation
        $variance = 0.0;
        foreach ($scores as $s) {
            $variance += pow($s - $mean, 2);
        }
        $stdDev = sqrt($variance / $count);

        return [
            'exam_id' => $examId,
            'count' => $count,
            'mean' => round($mean, 2),
            'median' => round($median, 2),
            'standard_deviation' => round($stdDev, 2),
            'skewness' => -0.24, // slightly left-skewed (high scores)
            'buckets' => [
                '0-60' => 0,
                '61-70' => 2,
                '71-80' => 4,
                '81-90' => 5,
                '91-100' => 5,
            ],
            'scores' => $scores,
        ];
    }

    /**
     * Get polar radar chart (6 dimensions).
     */
    public function getStudentRadarChart(int $studentId): array
    {
        return [
            'student_id' => $studentId,
            'dimensions' => [
                ['axis' => 'Akademik', 'value' => 88],
                ['axis' => 'Ibadah & Diniyyah', 'value' => 95],
                ['axis' => 'Disiplin', 'value' => 92],
                ['axis' => 'Ekstrakurikuler', 'value' => 85],
                ['axis' => 'Karakter & Akhlak', 'value' => 96],
                ['axis' => 'Kepemimpinan', 'value' => 82],
            ],
            'benchmark_average' => [
                ['axis' => 'Akademik', 'value' => 80],
                ['axis' => 'Ibadah & Diniyyah', 'value' => 84],
                ['axis' => 'Disiplin', 'value' => 85],
                ['axis' => 'Ekstrakurikuler', 'value' => 78],
                ['axis' => 'Karakter & Akhlak', 'value' => 86],
                ['axis' => 'Kepemimpinan', 'value' => 75],
            ],
        ];
    }

    /**
     * Get 6-month value trend (Sparkline).
     */
    public function getValueTrend(int $studentId): array
    {
        return [
            'student_id' => $studentId,
            'metric' => 'Rata-rata Nilai',
            'points' => [82.5, 84.0, 83.8, 86.2, 88.0, 89.5],
            'months' => ['Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep'],
            'trend_percentage' => '+8.5%',
            'is_positive' => true,
        ];
    }

    /**
     * Class-level benchmark comparisons.
     */
    public function getClassComparison(): array
    {
        return [
            [
                'className' => 'XII MIPA 1',
                'averageGrade' => 88.4,
                'attendanceRate' => 97.5,
                'disciplineIndex' => 96.0,
            ],
            [
                'className' => 'XII MIPA 2',
                'averageGrade' => 86.8,
                'attendanceRate' => 96.2,
                'disciplineIndex' => 94.5,
            ],
            [
                'className' => 'XII IPS 1',
                'averageGrade' => 85.0,
                'attendanceRate' => 95.0,
                'disciplineIndex' => 93.0,
            ],
        ];
    }

    /**
     * Live operational counters with short caching.
     */
    public function getLiveCounters(): array
    {
        return Cache::remember('viz_live_counters', 60, function () {
            return [
                'active_students_online' => 342,
                'attendance_today_percentage' => 98.4,
                'active_cbt_exams' => 4,
                'unpaid_billings_count' => 18,
                'last_updated' => Carbon::now('Asia/Jakarta')->format('H:i:s WIB'),
            ];
        });
    }
}
