<?php

namespace App\Services;

use App\Models\AcademicYear;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DataVisualizationService
{
    /**
     * Get 60-day attendance heatmap data from real attendance records.
     */
    public function getAttendanceHeatmap(): array
    {
        $heatmap = [];
        $today = Carbon::today();
        $startDate = $today->copy()->subDays(59)->toDateString();
        $endDate = $today->toDateString();

        $rows = DB::table('attendances')
            ->whereBetween('date', [$startDate, $endDate])
            ->get(['date', 'status'])
            ->groupBy(fn ($row) => substr((string) $row->date, 0, 10));

        for ($i = 59; $i >= 0; $i--) {
            $dateStr = $today->copy()->subDays($i)->toDateString();
            $dayRows = $rows->get($dateStr);

            if ($dayRows && $dayRows->isNotEmpty()) {
                $total = $dayRows->count();
                $present = $dayRows->whereIn('status', ['H', 'Hadir'])->count();
                $attendanceRate = round(($present / $total) * 100, 1);
                $level = match (true) {
                    $attendanceRate >= 98.0 => 4,
                    $attendanceRate >= 96.0 => 3,
                    $attendanceRate >= 93.0 => 2,
                    $attendanceRate > 0.0 => 1,
                    default => 0,
                };
            } else {
                $attendanceRate = 0.0;
                $level = 0;
            }

            $heatmap[$dateStr] = [
                'date' => $dateStr,
                'rate' => $attendanceRate,
                'level' => $level,
            ];
        }

        return $heatmap;
    }

    /**
     * Get statistical grade distribution for an exam from scored attempts.
     */
    public function getGradeDistribution(int $examId): array
    {
        $scores = DB::table('exam_attempts')
            ->where('exam_id', $examId)
            ->whereNotNull('score')
            ->orderBy('score')
            ->pluck('score')
            ->map(fn ($score) => round((float) $score, 2))
            ->values()
            ->all();

        sort($scores);
        $count = count($scores);

        $buckets = [
            '0-60' => 0,
            '61-70' => 0,
            '71-80' => 0,
            '81-90' => 0,
            '91-100' => 0,
        ];

        if ($count === 0) {
            return [
                'exam_id' => $examId,
                'count' => 0,
                'mean' => 0.0,
                'median' => 0.0,
                'standard_deviation' => 0.0,
                'skewness' => 0.0,
                'buckets' => $buckets,
                'scores' => [],
            ];
        }

        $mean = array_sum($scores) / $count;
        $mid = intdiv($count, 2);
        $median = ($count % 2 === 0)
            ? ($scores[$mid - 1] + $scores[$mid]) / 2
            : $scores[$mid];

        $variance = 0.0;
        $thirdMoment = 0.0;

        foreach ($scores as $score) {
            $diff = $score - $mean;
            $variance += $diff * $diff;
            $thirdMoment += $diff * $diff * $diff;

            if ($score <= 60) {
                $buckets['0-60']++;
            } elseif ($score <= 70) {
                $buckets['61-70']++;
            } elseif ($score <= 80) {
                $buckets['71-80']++;
            } elseif ($score <= 90) {
                $buckets['81-90']++;
            } else {
                $buckets['91-100']++;
            }
        }

        $stdDev = sqrt($variance / $count);
        $skewness = $stdDev > 0.0
            ? ($thirdMoment / $count) / pow($stdDev, 3)
            : 0.0;

        return [
            'exam_id' => $examId,
            'count' => $count,
            'mean' => round($mean, 2),
            'median' => round($median, 2),
            'standard_deviation' => round($stdDev, 2),
            'skewness' => round($skewness, 2),
            'buckets' => $buckets,
            'scores' => $scores,
        ];
    }

    /**
     * Get polar radar chart (6 dimensions) from actual student and cohort data.
     */
    public function getStudentRadarChart(int $studentId): array
    {
        $student = DB::table('students')->where('id', $studentId)->first(['id', 'user_id']);
        $userId = $student ? (int) $student->user_id : null;

        $academicValue = $this->computeStudentAcademicScore($studentId);
        $ibadahValue = $this->computeStudentIbadahScore($studentId);
        $disciplineValue = $this->computeStudentDisciplineScore($studentId);
        $ekskulValue = $this->computeStudentExtracurricularScore($studentId);
        $characterValue = $userId ? $this->computeUserCharacterScore($userId) : 0.0;
        $leadershipValue = $this->computeStudentLeadershipScore($studentId, $userId);

        return [
            'student_id' => $studentId,
            'dimensions' => [
                ['axis' => 'Akademik', 'value' => $academicValue],
                ['axis' => 'Ibadah & Diniyyah', 'value' => $ibadahValue],
                ['axis' => 'Disiplin', 'value' => $disciplineValue],
                ['axis' => 'Ekstrakurikuler', 'value' => $ekskulValue],
                ['axis' => 'Karakter & Akhlak', 'value' => $characterValue],
                ['axis' => 'Kepemimpinan', 'value' => $leadershipValue],
            ],
            'benchmark_average' => [
                ['axis' => 'Akademik', 'value' => $this->computeBenchmarkAcademicScore()],
                ['axis' => 'Ibadah & Diniyyah', 'value' => $this->computeBenchmarkIbadahScore()],
                ['axis' => 'Disiplin', 'value' => $this->computeBenchmarkDisciplineScore()],
                ['axis' => 'Ekstrakurikuler', 'value' => $this->computeBenchmarkExtracurricularScore()],
                ['axis' => 'Karakter & Akhlak', 'value' => $this->computeBenchmarkCharacterScore()],
                ['axis' => 'Kepemimpinan', 'value' => $this->computeBenchmarkLeadershipScore()],
            ],
        ];
    }

    /**
     * Get 6-month grade value trend (Sparkline) for a student.
     */
    public function getValueTrend(int $studentId): array
    {
        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $gradesByMonth = DB::table('grades')
            ->where('student_id', $studentId)
            ->get(['score', 'created_at'])
            ->filter(fn ($row) => $row->created_at !== null)
            ->groupBy(fn ($row) => substr((string) $row->created_at, 0, 7))
            ->map(fn (Collection $rows) => round((float) $rows->avg('score'), 1));

        $points = [];
        $months = [];
        $anchor = Carbon::today()->startOfMonth();

        for ($i = 5; $i >= 0; $i--) {
            $cursor = $anchor->copy()->subMonths($i);
            $ym = $cursor->format('Y-m');
            $months[] = $monthNames[(int) $cursor->month];
            $points[] = $gradesByMonth->has($ym) ? (float) $gradesByMonth->get($ym) : 0.0;
        }

        $nonZero = array_values(array_filter($points, fn (float $v) => $v > 0.0));
        $trendPct = 0.0;
        if (count($nonZero) >= 2 && $nonZero[0] > 0.0) {
            $first = $nonZero[0];
            $last = $nonZero[count($nonZero) - 1];
            $trendPct = round((($last - $first) / $first) * 100, 1);
        }

        $sign = $trendPct >= 0.0 ? '+' : '';

        return [
            'student_id' => $studentId,
            'metric' => 'Rata-rata Nilai',
            'points' => $points,
            'months' => $months,
            'trend_percentage' => $sign.number_format($trendPct, 1, '.', '').'%',
            'is_positive' => $trendPct >= 0.0,
        ];
    }

    /**
     * Class-level benchmark comparisons from active classrooms in DB.
     */
    public function getClassComparison(): array
    {
        $activeYear = AcademicYear::current();

        $classroomsQuery = DB::table('classrooms')->orderBy('name');
        if ($activeYear) {
            $scoped = (clone $classroomsQuery)->where('academic_year', $activeYear->year)->get(['id', 'name']);
            $classrooms = $scoped->isNotEmpty() ? $scoped : $classroomsQuery->get(['id', 'name']);
        } else {
            $classrooms = $classroomsQuery->get(['id', 'name']);
        }

        if ($classrooms->isEmpty()) {
            return [];
        }

        $studentsByClass = DB::table('students')
            ->whereIn('classroom_id', $classrooms->pluck('id'))
            ->whereNull('deleted_at')
            ->get(['id', 'classroom_id'])
            ->groupBy('classroom_id');

        $allAttendances = DB::table('attendances')
            ->whereIn('classroom_id', $classrooms->pluck('id'))
            ->when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
            ->get(['classroom_id', 'status'])
            ->groupBy('classroom_id');

        $result = [];
        foreach ($classrooms as $classroom) {
            $studentIds = ($studentsByClass->get($classroom->id) ?? collect())->pluck('id')->all();

            $averageGrade = 0.0;
            $disciplineIndex = 0.0;

            if ($studentIds !== []) {
                $avg = DB::table('grades')
                    ->whereIn('student_id', $studentIds)
                    ->when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
                    ->avg('score');
                $averageGrade = round((float) ($avg ?? 0.0), 1);

                $disciplineScores = [];
                foreach ($studentIds as $sId) {
                    $score = $this->computeStudentDisciplineScore((int) $sId);
                    if ($score > 0.0 || DB::table('student_point_logs')->where('student_id', $sId)->exists()) {
                        $disciplineScores[] = $score;
                    }
                }
                if ($disciplineScores !== []) {
                    $disciplineIndex = round(array_sum($disciplineScores) / count($disciplineScores), 1);
                }
            }

            $attRows = $allAttendances->get($classroom->id) ?? collect();
            $attTotal = $attRows->count();
            $attPresent = $attRows->whereIn('status', ['H', 'Hadir'])->count();
            $attendanceRate = $attTotal > 0 ? round(($attPresent / $attTotal) * 100, 1) : 0.0;

            $result[] = [
                'className' => $classroom->name,
                'averageGrade' => $averageGrade,
                'attendanceRate' => $attendanceRate,
                'disciplineIndex' => $disciplineIndex,
            ];
        }

        return $result;
    }

    /**
     * Live operational counters queried directly from database.
     */
    public function getLiveCounters(): array
    {
        $now = Carbon::now();
        $today = $now->toDateString();

        $activeStudentsOnline = DB::table('active_sessions')
            ->where('last_activity_at', '>=', $now->copy()->subMinutes(15))
            ->distinct()
            ->count('user_id');

        $todayAttendances = DB::table('attendances')
            ->where('date', $today)
            ->get(['status']);
        $todayTotal = $todayAttendances->count();
        $todayPresent = $todayAttendances->whereIn('status', ['H', 'Hadir'])->count();
        $attendanceTodayPct = $todayTotal > 0 ? round(($todayPresent / $todayTotal) * 100, 1) : 0.0;

        $activeCbtExams = DB::table('exams')
            ->whereNull('deleted_at')
            ->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('start_time')->orWhere('start_time', '<=', $now))
            ->where(fn ($q) => $q->whereNull('end_time')->orWhere('end_time', '>=', $now))
            ->count();

        $unpaidBillingsCount = DB::table('billings')
            ->whereNull('deleted_at')
            ->whereIn('status', ['unpaid', 'partial'])
            ->count();

        return [
            'active_students_online' => (int) $activeStudentsOnline,
            'attendance_today_percentage' => $attendanceTodayPct,
            'active_cbt_exams' => (int) $activeCbtExams,
            'unpaid_billings_count' => (int) $unpaidBillingsCount,
            'last_updated' => Carbon::now('Asia/Jakarta')->format('H:i:s').' WIB',
        ];
    }

    private function computeStudentAcademicScore(int $studentId): float
    {
        $avg = DB::table('grades')->where('student_id', $studentId)->avg('score');
        if ($avg === null) {
            $avg = DB::table('exam_attempts')
                ->where('student_id', $studentId)
                ->whereNotNull('score')
                ->avg('score');
        }

        return round((float) ($avg ?? 0.0), 1);
    }

    private function computeBenchmarkAcademicScore(): float
    {
        $avg = DB::table('grades')->avg('score');
        if ($avg === null) {
            $avg = DB::table('exam_attempts')->whereNotNull('score')->avg('score');
        }

        return round((float) ($avg ?? 0.0), 1);
    }

    private function computeStudentIbadahScore(int $studentId): float
    {
        $rows = DB::table('mutabaah_logs')
            ->where('student_id', $studentId)
            ->get(['mutabaah_item_id', 'score', 'is_completed']);

        if ($rows->isEmpty()) {
            return 0.0;
        }

        return round((float) $rows->avg(function ($row) {
            if ($row->mutabaah_item_id === null || (float) $row->score > 0.0) {
                return (float) $row->score;
            }

            return $row->is_completed ? 100.0 : 0.0;
        }), 1);
    }

    private function computeBenchmarkIbadahScore(): float
    {
        $rows = DB::table('mutabaah_logs')->get(['mutabaah_item_id', 'score', 'is_completed']);
        if ($rows->isEmpty()) {
            return 0.0;
        }

        return round((float) $rows->avg(function ($row) {
            if ($row->mutabaah_item_id === null || (float) $row->score > 0.0) {
                return (float) $row->score;
            }

            return $row->is_completed ? 100.0 : 0.0;
        }), 1);
    }

    private function computeStudentDisciplineScore(int $studentId): float
    {
        $logs = DB::table('student_point_logs')
            ->where('student_id', $studentId)
            ->get(['points', 'type']);
        $hasAttendance = DB::table('attendances')->where('student_id', $studentId)->exists();

        if ($logs->isEmpty() && ! $hasAttendance) {
            return 0.0;
        }

        $penalty = (float) $logs->where('type', 'penalty')->sum(fn ($r) => abs((float) $r->points));
        $reward = (float) $logs->whereIn('type', ['reward', 'adjustment'])->sum(fn ($r) => abs((float) $r->points));
        $netPenalty = max(0.0, $penalty - $reward);

        return round(max(0.0, min(100.0, 100.0 - $netPenalty)), 1);
    }

    private function computeBenchmarkDisciplineScore(): float
    {
        $studentIds = DB::table('student_point_logs')->distinct()->pluck('student_id')
            ->merge(DB::table('attendances')->distinct()->pluck('student_id'))
            ->unique()
            ->values();

        if ($studentIds->isEmpty()) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($studentIds as $sId) {
            $total += $this->computeStudentDisciplineScore((int) $sId);
        }

        return round($total / $studentIds->count(), 1);
    }

    private function computeStudentExtracurricularScore(int $studentId): float
    {
        $gradeMap = ['A' => 95.0, 'B' => 85.0, 'C' => 75.0, 'D' => 65.0];

        $grades = DB::table('extracurricular_grades')
            ->join('extracurricular_members', 'extracurricular_grades.extracurricular_member_id', '=', 'extracurricular_members.id')
            ->where('extracurricular_members.student_id', $studentId)
            ->whereNotNull('extracurricular_grades.grade')
            ->pluck('extracurricular_grades.grade');

        if ($grades->isNotEmpty()) {
            return round((float) $grades->avg(fn ($g) => $gradeMap[(string) $g] ?? 70.0), 1);
        }

        $attRows = DB::table('extracurricular_attendances')
            ->where('student_id', $studentId)
            ->get(['status']);
        if ($attRows->isNotEmpty()) {
            $present = $attRows->whereIn('status', ['H', 'Hadir'])->count();

            return round(($present / $attRows->count()) * 100, 1);
        }

        $isMember = DB::table('extracurricular_members')
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->exists();

        return $isMember ? 75.0 : 0.0;
    }

    private function computeBenchmarkExtracurricularScore(): float
    {
        $studentIds = DB::table('extracurricular_members')
            ->where('status', 'active')
            ->distinct()
            ->pluck('student_id')
            ->merge(DB::table('extracurricular_attendances')->distinct()->pluck('student_id'))
            ->unique()
            ->values();

        if ($studentIds->isEmpty()) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($studentIds as $sId) {
            $total += $this->computeStudentExtracurricularScore((int) $sId);
        }

        return round($total / $studentIds->count(), 1);
    }

    private function computeUserCharacterScore(int $userId): float
    {
        $avg = DB::table('character_assessments')
            ->where('student_id', $userId)
            ->avg('score');

        if ($avg !== null) {
            return round(min(100.0, ((float) $avg / 4.0) * 100.0), 1);
        }

        $grades = DB::table('report_card_characters')
            ->where('student_id', $userId)
            ->pluck('grade');

        if ($grades->isEmpty()) {
            return 0.0;
        }

        $gradeMap = [
            'Sangat Baik' => 95.0,
            'Baik' => 85.0,
            'Cukup' => 75.0,
            'Perlu Bimbingan' => 60.0,
        ];

        return round((float) $grades->avg(fn ($g) => $gradeMap[(string) $g] ?? 75.0), 1);
    }

    private function computeBenchmarkCharacterScore(): float
    {
        $avg = DB::table('character_assessments')->avg('score');
        if ($avg !== null) {
            return round(min(100.0, ((float) $avg / 4.0) * 100.0), 1);
        }

        $grades = DB::table('report_card_characters')->pluck('grade');
        if ($grades->isEmpty()) {
            return 0.0;
        }

        $gradeMap = [
            'Sangat Baik' => 95.0,
            'Baik' => 85.0,
            'Cukup' => 75.0,
            'Perlu Bimbingan' => 60.0,
        ];

        return round((float) $grades->avg(fn ($g) => $gradeMap[(string) $g] ?? 75.0), 1);
    }

    private function computeStudentLeadershipScore(int $studentId, ?int $userId): float
    {
        $score = 0.0;
        $hasData = false;

        $osis = DB::table('osis_members')
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->get(['position']);
        if ($osis->isNotEmpty()) {
            $hasData = true;
            $hasOfficer = $osis->contains(fn ($m) => stripos((string) $m->position, 'anggota') === false);
            $score += $hasOfficer ? 90.0 : 80.0;
        }

        $ekskulLeader = DB::table('extracurricular_members')
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->whereIn('role', ['ketua', 'wakil_ketua', 'sekretaris', 'bendahara'])
            ->exists();
        if ($ekskulLeader) {
            $hasData = true;
            $score = max($score, 85.0);
        }

        if ($userId !== null) {
            $meritSum = (float) DB::table('merit_points')
                ->where('student_id', $userId)
                ->sum('points');
            $hasMerit = DB::table('merit_points')->where('student_id', $userId)->exists();
            if ($hasMerit) {
                $hasData = true;
                $score += max(0.0, $meritSum);
            }
        }

        return $hasData ? round(min(100.0, $score), 1) : 0.0;
    }

    private function computeBenchmarkLeadershipScore(): float
    {
        $studentIds = DB::table('osis_members')->where('status', 'active')->distinct()->pluck('student_id')
            ->merge(DB::table('extracurricular_members')->where('status', 'active')->whereIn('role', ['ketua', 'wakil_ketua', 'sekretaris', 'bendahara'])->distinct()->pluck('student_id'));

        $meritUserIds = DB::table('merit_points')->distinct()->pluck('student_id');
        if ($meritUserIds->isNotEmpty()) {
            $studentIds = $studentIds->merge(
                DB::table('students')->whereIn('user_id', $meritUserIds)->pluck('id')
            );
        }

        $studentIds = $studentIds->unique()->values();
        if ($studentIds->isEmpty()) {
            return 0.0;
        }

        $students = DB::table('students')->whereIn('id', $studentIds)->pluck('user_id', 'id');
        $total = 0.0;
        foreach ($studentIds as $sId) {
            $userId = $students->get($sId);
            $total += $this->computeStudentLeadershipScore((int) $sId, $userId ? (int) $userId : null);
        }

        return round($total / $studentIds->count(), 1);
    }
}
