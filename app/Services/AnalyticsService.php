<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    protected const TTL = 60 * 30; // 30 minutes caching

    /**
     * Get attendance trend for the last N months.
     */
    public function getAttendanceTrend(int $months = 6): Collection
    {
        $cacheKey = 'analytics_attendance_trend_'.$months;
        $data = Cache::get($cacheKey);

        if ($data === null || $data instanceof \__PHP_Incomplete_Class || ! is_array($data)) {
            $driver = DB::getDriverName();
            $monthExpression = $driver === 'sqlite' ? "strftime('%Y-%m', date)" : "DATE_FORMAT(date, '%Y-%m')";

            $rawAttendances = DB::table('attendances')
                ->selectRaw("{$monthExpression} as month, status, count(*) as count")
                ->where('date', '>=', now()->subMonths($months)->startOfMonth())
                ->groupBy('month', 'status')
                ->orderBy('month')
                ->get();

            // Structure data by month
            $monthsData = [];

            // Pre-populate last N months to ensure no gaps
            for ($i = $months - 1; $i >= 0; $i--) {
                $m = now()->subMonths($i)->format('Y-m');
                $monthsData[$m] = [
                    'month' => $m,
                    'H' => 0,
                    'S' => 0,
                    'I' => 0,
                    'A' => 0,
                    'total' => 0,
                    'percentage' => 0.0,
                ];
            }

            foreach ($rawAttendances as $row) {
                if (isset($monthsData[$row->month])) {
                    $status = $row->status;
                    $monthsData[$row->month][$status] = (int) $row->count;
                    $monthsData[$row->month]['total'] += (int) $row->count;
                }
            }

            foreach ($monthsData as $m => &$item) {
                if ($item['total'] > 0) {
                    $item['percentage'] = round(($item['H'] / $item['total']) * 100, 2);
                } else {
                    $item['percentage'] = 0.0;
                }
            }

            $data = array_values($monthsData);
            Cache::put($cacheKey, $data, self::TTL);
        }

        return collect($data);
    }

    /**
     * Get top subjects and grade distributions.
     */
    public function getGradeDistribution(?int $classroomId = null): array
    {
        $cacheKey = 'analytics_grade_distribution_'.($classroomId ?? 'global');
        $data = Cache::get($cacheKey);

        if ($data === null || $data instanceof \__PHP_Incomplete_Class || ! is_array($data)) {
            // Top 10 Subjects Average
            $topQuery = DB::table('grades')
                ->join('subjects', 'grades.subject_id', '=', 'subjects.id')
                ->select('subjects.name', DB::raw('ROUND(AVG(grades.score), 2) as average'))
                ->groupBy('subjects.id', 'subjects.name');

            if ($classroomId) {
                $topQuery->join('students', 'grades.student_id', '=', 'students.id')
                    ->where('students.classroom_id', $classroomId);
            }

            $topSubjects = $topQuery->orderByDesc('average')
                ->limit(10)
                ->get()
                ->map(fn ($item) => (array) $item)
                ->all();

            // Distribution score ranges
            $distQuery = DB::table('grades');
            if ($classroomId) {
                $distQuery->join('students', 'grades.student_id', '=', 'students.id')
                    ->where('students.classroom_id', $classroomId);
            }

            $distributionRaw = $distQuery->selectRaw('
                SUM(CASE WHEN score < 60 THEN 1 ELSE 0 END) as under_60,
                SUM(CASE WHEN score >= 60 AND score < 70 THEN 1 ELSE 0 END) as score_60_70,
                SUM(CASE WHEN score >= 70 AND score < 80 THEN 1 ELSE 0 END) as score_70_80,
                SUM(CASE WHEN score >= 80 AND score < 90 THEN 1 ELSE 0 END) as score_80_90,
                SUM(CASE WHEN score >= 90 THEN 1 ELSE 0 END) as score_90_100
            ')->first();

            // Classroom averages
            $classAverages = [];
            if (! $classroomId) {
                $classAverages = DB::table('grades')
                    ->join('students', 'grades.student_id', '=', 'students.id')
                    ->join('classrooms', 'students.classroom_id', '=', 'classrooms.id')
                    ->select('classrooms.name', DB::raw('ROUND(AVG(grades.score), 2) as average'))
                    ->groupBy('classrooms.id', 'classrooms.name')
                    ->orderBy('classrooms.name')
                    ->get()
                    ->map(fn ($item) => (array) $item)
                    ->all();
            }

            $data = [
                'top_subjects' => $topSubjects,
                'distribution' => [
                    '< 60' => (int) ($distributionRaw->under_60 ?? 0),
                    '60-70' => (int) ($distributionRaw->score_60_70 ?? 0),
                    '70-80' => (int) ($distributionRaw->score_70_80 ?? 0),
                    '80-90' => (int) ($distributionRaw->score_80_90 ?? 0),
                    '90-100' => (int) ($distributionRaw->score_90_100 ?? 0),
                ],
                'class_averages' => $classAverages,
            ];
            Cache::put($cacheKey, $data, self::TTL);
        }

        if (isset($data['top_subjects'])) {
            $data['top_subjects'] = array_map(fn ($item) => (object) $item, $data['top_subjects']);
        }
        if (isset($data['class_averages'])) {
            $data['class_averages'] = array_map(fn ($item) => (object) $item, $data['class_averages']);
        }

        return $data;
    }

    /**
     * Get financial status globally and by classroom.
     */
    public function getFinancialOverview(): array
    {
        $cacheKey = 'analytics_financial_overview';
        $data = Cache::get($cacheKey);

        if ($data === null || $data instanceof \__PHP_Incomplete_Class || ! is_array($data)) {
            // Global overview
            $globalRaw = DB::table('billings')
                ->selectRaw('SUM(amount) as total, SUM(amount - remaining_balance) as paid, SUM(remaining_balance) as unpaid')
                ->first();

            $total = (float) ($globalRaw->total ?? 0);
            $paid = (float) ($globalRaw->paid ?? 0);
            $unpaid = (float) ($globalRaw->unpaid ?? 0);

            $global = [
                'total' => $total,
                'paid' => $paid,
                'unpaid' => $unpaid,
                'paid_percentage' => $total > 0 ? round(($paid / $total) * 100, 2) : 0.0,
                'unpaid_percentage' => $total > 0 ? round(($unpaid / $total) * 100, 2) : 0.0,
            ];

            // By Classroom
            $byClassroomRaw = DB::table('billings')
                ->join('students', 'billings.student_id', '=', 'students.id')
                ->join('classrooms', 'students.classroom_id', '=', 'classrooms.id')
                ->select(
                    'classrooms.name',
                    DB::raw('SUM(billings.amount) as total'),
                    DB::raw('SUM(billings.amount - billings.remaining_balance) as paid'),
                    DB::raw('SUM(billings.remaining_balance) as unpaid')
                )
                ->groupBy('classrooms.id', 'classrooms.name')
                ->orderBy('classrooms.name')
                ->get();

            $byClassroom = $byClassroomRaw->map(function ($row) {
                $total = (float) $row->total;
                $paid = (float) $row->paid;

                return [
                    'classroom' => $row->name,
                    'total' => $total,
                    'paid' => $paid,
                    'unpaid' => (float) $row->unpaid,
                    'paid_percentage' => $total > 0 ? round(($paid / $total) * 100, 2) : 0.0,
                ];
            })->all();

            $data = [
                'global' => $global,
                'by_classroom' => $byClassroom,
            ];
            Cache::put($cacheKey, $data, self::TTL);
        }

        return $data;
    }

    /**
     * Get BK penalties vs rewards point statistics.
     */
    public function getPointStatistics(): array
    {
        $cacheKey = 'analytics_point_statistics';
        $data = Cache::get($cacheKey);

        if ($data === null || $data instanceof \__PHP_Incomplete_Class || ! is_array($data)) {
            // Global points
            $globalRaw = DB::table('student_point_logs')
                ->selectRaw("
                    SUM(CASE WHEN type = 'penalty' THEN points ELSE 0 END) as penalty_points,
                    SUM(CASE WHEN type = 'reward' THEN ABS(points) ELSE 0 END) as reward_points,
                    COUNT(CASE WHEN type = 'penalty' THEN 1 END) as penalty_count,
                    COUNT(CASE WHEN type = 'reward' THEN 1 END) as reward_count
                ")
                ->first();

            $global = [
                'penalty_points' => (int) ($globalRaw->penalty_points ?? 0),
                'reward_points' => (int) ($globalRaw->reward_points ?? 0),
                'penalty_count' => (int) ($globalRaw->penalty_count ?? 0),
                'reward_count' => (int) ($globalRaw->reward_count ?? 0),
            ];

            // By Classroom
            $byClassroomRaw = DB::table('student_point_logs')
                ->join('students', 'student_point_logs.student_id', '=', 'students.id')
                ->join('classrooms', 'students.classroom_id', '=', 'classrooms.id')
                ->select(
                    'classrooms.name',
                    DB::raw("SUM(CASE WHEN student_point_logs.type = 'penalty' THEN student_point_logs.points ELSE 0 END) as penalty_points"),
                    DB::raw("SUM(CASE WHEN student_point_logs.type = 'reward' THEN ABS(student_point_logs.points) ELSE 0 END) as reward_points")
                )
                ->groupBy('classrooms.id', 'classrooms.name')
                ->orderBy('classrooms.name')
                ->get();

            $byClassroom = $byClassroomRaw->map(function ($row) {
                return [
                    'classroom' => $row->name,
                    'penalty_points' => (int) $row->penalty_points,
                    'reward_points' => (int) $row->reward_points,
                ];
            })->all();

            $data = [
                'global' => $global,
                'by_classroom' => $byClassroom,
            ];
            Cache::put($cacheKey, $data, self::TTL);
        }

        return $data;
    }

    /**
     * Get Elearning usage and score metrics.
     */
    public function getElearningMetrics(): array
    {
        $cacheKey = 'analytics_elearning_metrics';
        $data = Cache::get($cacheKey);

        if ($data === null || $data instanceof \__PHP_Incomplete_Class || ! is_array($data)) {
            $totalClasses = DB::table('elearning_classes')->count();
            $totalAssignments = DB::table('elearning_assignments')->count();

            $submissionsRaw = DB::table('elearning_submissions')
                ->selectRaw('COUNT(*) as total, AVG(score) as avg_score, SUM(CASE WHEN violation_count > 0 THEN 1 ELSE 0 END) as with_violations')
                ->first();

            $totalSubmissions = (int) ($submissionsRaw->total ?? 0);
            $avgScore = round((float) ($submissionsRaw->avg_score ?? 0.0), 2);
            $withViolations = (int) ($submissionsRaw->with_violations ?? 0);

            // Active teachers in elearning
            $activeTeachers = DB::table('elearning_classes')
                ->join('users', 'elearning_classes.teacher_id', '=', 'users.id')
                ->select('users.name', DB::raw('COUNT(*) as total_classes'))
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total_classes')
                ->limit(5)
                ->get()
                ->map(fn ($item) => (array) $item)
                ->all();

            $data = [
                'total_classes' => $totalClasses,
                'total_assignments' => $totalAssignments,
                'total_submissions' => $totalSubmissions,
                'average_score' => $avgScore,
                'violations_count' => $withViolations,
                'clean_count' => max(0, $totalSubmissions - $withViolations),
                'active_teachers' => $activeTeachers,
            ];
            Cache::put($cacheKey, $data, self::TTL);
        }

        if (isset($data['active_teachers'])) {
            $data['active_teachers'] = array_map(fn ($item) => (object) $item, $data['active_teachers']);
        }

        return $data;
    }

    /**
     * Get SPMB year-over-year growth comparison.
     */
    public function getSpmbComparison(): array
    {
        $cacheKey = 'analytics_spmb_comparison';
        $data = Cache::get($cacheKey);

        if ($data === null || $data instanceof \__PHP_Incomplete_Class || ! is_array($data)) {
            $driver = DB::getDriverName();
            $yearExpression = $driver === 'sqlite' ? "strftime('%Y', created_at)" : 'YEAR(created_at)';

            $spmbGrowth = DB::table('registrations')
                ->selectRaw("{$yearExpression} as year, COUNT(*) as total")
                ->groupBy('year')
                ->orderBy('year')
                ->get()
                ->map(fn ($item) => (array) $item)
                ->all();

            $data = [
                'growth' => $spmbGrowth,
            ];
            Cache::put($cacheKey, $data, self::TTL);
        }

        if (isset($data['growth'])) {
            $data['growth'] = array_map(fn ($item) => (object) $item, $data['growth']);
        }

        return $data;
    }

    /**
     * Get Teacher performance journal metrics.
     */
    public function getTeacherPerformanceMetrics(): array
    {
        $cacheKey = 'analytics_teacher_performance';
        $data = Cache::get($cacheKey);

        if ($data === null || $data instanceof \__PHP_Incomplete_Class || ! is_array($data)) {
            $teacherPerformance = DB::table('teaching_journals')
                ->join('users', 'teaching_journals.user_id', '=', 'users.id')
                ->select(
                    'users.name',
                    DB::raw('COUNT(*) as total_journals'),
                    DB::raw("SUM(CASE WHEN teaching_journals.status = 'reviewed' THEN 1 ELSE 0 END) as reviewed_journals")
                )
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total_journals')
                ->limit(10)
                ->get()
                ->map(fn ($item) => (array) $item)
                ->all();

            $data = [
                'performance' => $teacherPerformance,
            ];
            Cache::put($cacheKey, $data, self::TTL);
        }

        if (isset($data['performance'])) {
            $data['performance'] = array_map(fn ($item) => (object) $item, $data['performance']);
        }

        return $data;
    }

    /**
     * Clear all analytics cache.
     */
    public function clearCache(): void
    {
        Cache::forget('analytics_attendance_trend_6');
        Cache::forget('analytics_grade_distribution_global');
        Cache::forget('analytics_financial_overview');
        Cache::forget('analytics_point_statistics');
        Cache::forget('analytics_elearning_metrics');
        Cache::forget('analytics_spmb_comparison');
        Cache::forget('analytics_teacher_performance');
    }
}
