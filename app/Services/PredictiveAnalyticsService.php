<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PredictiveAnalyticsService
{
    protected const TTL = 60 * 30; // 30 minutes caching

    /**
     * Identify students with high risk scores (> 40%).
     */
    public function identifyAtRiskStudents(?int $classroomId = null): Collection
    {
        $cacheKey = 'analytics_at_risk_students_'.($classroomId ?? 'global');
        $data = Cache::get($cacheKey);

        if ($data === null || $data instanceof \__PHP_Incomplete_Class) {
            $query = Student::with(['classroom', 'user'])->where('status', 'active');

            if ($classroomId) {
                $query->where('classroom_id', $classroomId);
            }

            $students = $query->get();
            $atRiskList = collect();

            foreach ($students as $student) {
                $riskData = $this->calculateRiskScore($student);
                if ($riskData['total_risk_score'] >= 40) { // Threshold for early warning
                    $riskData['student'] = $student;
                    $atRiskList->push((object) $riskData);
                }
            }

            // Sort by highest risk first
            $data = $atRiskList->sortByDesc('total_risk_score')->values();
            Cache::put($cacheKey, $data, self::TTL);
        }

        return $data;
    }

    /**
     * Calculate comprehensive risk score for a single student.
     * Weights: Academic (40%), Attendance (30%), Behavior/Points (30%)
     */
    public function calculateRiskScore(Student $student): array
    {
        // 1. Academic Factor (0-100)
        $avgGrade = DB::table('grades')->where('student_id', $student->id)->avg('score');
        $academicRisk = 0;
        if ($avgGrade !== null) {
            if ($avgGrade < 60) {
                $academicRisk = 100;
            } elseif ($avgGrade < 70) {
                $academicRisk = 75;
            } elseif ($avgGrade < 80) {
                $academicRisk = 40;
            } elseif ($avgGrade < 90) {
                $academicRisk = 10;
            }
        }

        // 2. Attendance Factor (0-100)
        $attendances = DB::table('attendances')->where('student_id', $student->id)->get();
        $totalDays = $attendances->count();
        $attendanceRisk = 0;
        $attendanceRate = 100;

        if ($totalDays > 0) {
            $presentDays = $attendances->where('status', 'H')->count();
            $attendanceRate = ($presentDays / $totalDays) * 100;

            if ($attendanceRate < 70) {
                $attendanceRisk = 100;
            } elseif ($attendanceRate < 80) {
                $attendanceRisk = 80;
            } elseif ($attendanceRate < 90) {
                $attendanceRisk = 50;
            } elseif ($attendanceRate < 95) {
                $attendanceRisk = 20;
            }
        }

        // 3. Behavior/Points Factor (0-100)
        $penaltyPoints = DB::table('student_point_logs')
            ->where('student_id', $student->id)
            ->where('type', 'penalty')
            ->sum('points');
        $behaviorRisk = 0;
        if ($penaltyPoints >= 100) {
            $behaviorRisk = 100;
        } elseif ($penaltyPoints >= 75) {
            $behaviorRisk = 75;
        } elseif ($penaltyPoints >= 50) {
            $behaviorRisk = 50;
        } elseif ($penaltyPoints >= 20) {
            $behaviorRisk = 25;
        }

        // Weighted Total Risk Score
        $totalRiskScore = ($academicRisk * 0.4) + ($attendanceRisk * 0.3) + ($behaviorRisk * 0.3);

        // Determine Category
        if ($totalRiskScore >= 75) {
            $category = 'Kritis';
            $color = 'danger';
            $recommendation = 'Panggilan Orang Tua & Konseling Intensif';
        } elseif ($totalRiskScore >= 40) {
            $category = 'Waspada';
            $color = 'warning';
            $recommendation = 'Pemantauan Wali Kelas & Pendampingan Akademik';
        } else {
            $category = 'Aman';
            $color = 'success';
            $recommendation = 'Pertahankan Prestasi';
        }

        return [
            'academic_risk' => round($academicRisk, 1),
            'attendance_risk' => round($attendanceRisk, 1),
            'behavior_risk' => round($behaviorRisk, 1),
            'total_risk_score' => round($totalRiskScore, 1),
            'average_grade' => $avgGrade !== null ? round($avgGrade, 1) : null,
            'attendance_rate' => round($attendanceRate, 1),
            'penalty_points' => $penaltyPoints,
            'category' => $category,
            'color' => $color,
            'recommendation' => $recommendation,
        ];
    }

    /**
     * Monthly trend of a metric over the last $months months, plus a 3-month
     * simple-moving-average projection.
     *
     * Supported metric: 'finance' = percentage of the billed amount that has
     * been paid, for billings due in that month. Months without billings are
     * null (no data), not a made-up value; the projection only uses real
     * months and is null when there are none. (This used to plot rand(80, 95).)
     *
     * @return list<array{month: string, actual: float|null, forecast: float|null}>
     */
    public function generateTrendForecast(string $metric, int $months = 6): array
    {
        if ($metric !== 'finance') {
            throw new \InvalidArgumentException("Metrik tren [{$metric}] belum memiliki sumber data.");
        }

        $start = now()->startOfMonth()->subMonths($months);
        $end = now()->startOfMonth()->subDay();

        $rates = DB::table('billings')
            ->whereBetween('due_date', [$start->toDateString(), $end->toDateString()])
            ->get(['due_date', 'amount', 'remaining_balance'])
            ->groupBy(fn ($row) => substr((string) $row->due_date, 0, 7))
            ->map(function ($rows) {
                $total = (float) $rows->sum('amount');

                return $total > 0 ? round(($total - (float) $rows->sum('remaining_balance')) / $total * 100, 1) : null;
            });

        $data = [];
        for ($i = $months; $i >= 1; $i--) {
            $month = now()->startOfMonth()->subMonths($i)->format('Y-m');
            $data[] = ['month' => $month, 'actual' => $rates[$month] ?? null, 'forecast' => null];
        }

        $history = array_values(array_filter(array_column($data, 'actual'), fn ($v) => $v !== null));
        for ($i = 0; $i < 3; $i++) {
            $window = array_slice($history, -3);
            $avg = $window === [] ? null : round(array_sum($window) / count($window), 1);
            $data[] = ['month' => now()->startOfMonth()->addMonths($i)->format('Y-m'), 'actual' => null, 'forecast' => $avg];
            if ($avg !== null) {
                $history[] = $avg;
            }
        }

        return $data;
    }

    /**
     * Get Student Performance Radar Data (6 Dimensions)
     */
    public function getStudentPerformanceRadar(Student $student): array
    {
        $riskData = $this->calculateRiskScore($student);

        // Invert risk to performance (100 = best)
        $academic = 100 - $riskData['academic_risk'];
        $attendance = 100 - $riskData['attendance_risk'];
        $behavior = 100 - $riskData['behavior_risk'];

        // No scoring source is defined yet for these three dimensions; they
        // are null (no data) instead of the random 50–100 values they used to be.
        $elearning = null;
        $tahfidz = null;
        $extracurricular = null;

        return [
            'labels' => ['Akademik', 'Kehadiran', 'Perilaku', 'E-Learning', 'Tahfidz', 'Ekstrakurikuler'],
            'data' => [$academic, $attendance, $behavior, $elearning, $tahfidz, $extracurricular],
        ];
    }
}
