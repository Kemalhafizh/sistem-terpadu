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
        $cacheKey = 'analytics_at_risk_students_' . ($classroomId ?? 'global');
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
            if ($avgGrade < 60) $academicRisk = 100;
            elseif ($avgGrade < 70) $academicRisk = 75;
            elseif ($avgGrade < 80) $academicRisk = 40;
            elseif ($avgGrade < 90) $academicRisk = 10;
        }

        // 2. Attendance Factor (0-100)
        $attendances = DB::table('attendances')->where('student_id', $student->id)->get();
        $totalDays = $attendances->count();
        $attendanceRisk = 0;
        $attendanceRate = 100;
        
        if ($totalDays > 0) {
            $presentDays = $attendances->where('status', 'H')->count();
            $attendanceRate = ($presentDays / $totalDays) * 100;
            
            if ($attendanceRate < 70) $attendanceRisk = 100;
            elseif ($attendanceRate < 80) $attendanceRisk = 80;
            elseif ($attendanceRate < 90) $attendanceRisk = 50;
            elseif ($attendanceRate < 95) $attendanceRisk = 20;
        }

        // 3. Behavior/Points Factor (0-100)
        $penaltyPoints = DB::table('student_point_logs')
                            ->where('student_id', $student->id)
                            ->where('type', 'penalty')
                            ->sum('points');
        $behaviorRisk = 0;
        if ($penaltyPoints >= 100) $behaviorRisk = 100;
        elseif ($penaltyPoints >= 75) $behaviorRisk = 75;
        elseif ($penaltyPoints >= 50) $behaviorRisk = 50;
        elseif ($penaltyPoints >= 20) $behaviorRisk = 25;

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
     * Generate trend forecast using simple moving average.
     */
    public function generateTrendForecast(string $metric, int $months = 6): array
    {
        // Mocking moving average calculation for demonstration based on metric type
        $data = [];
        $currentDate = now();

        for ($i = $months; $i >= 1; $i--) {
            $month = $currentDate->copy()->subMonths($i)->format('Y-m');
            // Generate some plausible historical data based on metric
            $val = $metric === 'finance' ? rand(80, 95) : rand(90, 98);
            $data[] = [
                'month' => $month,
                'actual' => $val,
                'forecast' => null,
            ];
        }

        // Calculate Simple Moving Average for next 3 months
        $historicalValues = array_column($data, 'actual');
        for ($i = 0; $i < 3; $i++) {
            $month = $currentDate->copy()->addMonths($i)->format('Y-m');
            $avg = count($historicalValues) > 0 ? array_sum(array_slice($historicalValues, -3)) / 3 : 90;
            $data[] = [
                'month' => $month,
                'actual' => null,
                'forecast' => round($avg, 1),
            ];
            $historicalValues[] = $avg;
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
        
        // Additional mock dimensions for complete radar
        $elearning = rand(60, 100); 
        $tahfidz = rand(50, 100);
        $extracurricular = rand(70, 100);

        return [
            'labels' => ['Akademik', 'Kehadiran', 'Perilaku', 'E-Learning', 'Tahfidz', 'Ekstrakurikuler'],
            'data' => [$academic, $attendance, $behavior, $elearning, $tahfidz, $extracurricular]
        ];
    }
}
