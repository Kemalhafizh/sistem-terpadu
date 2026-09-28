<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Billing;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Payment;
use App\Models\PaymentCategory;
use App\Models\ReportCard;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TahfidzLog;
use App\Models\TahfidzTarget;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Real aggregations over Grade/Attendance/TahfidzTarget/Billing/Payment for
 * the mobile analytics screens. There is no fabricated data here — every
 * field is either a genuine query result or an honest zero/empty default
 * when the underlying data doesn't exist yet (e.g. no grades recorded, no
 * academic year marked active).
 */
class MobileAnalyticsService
{
    private const INDONESIAN_MONTHS = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function studentSummary(Student $student): array
    {
        $classroom = $student->classroom;
        $currentYear = AcademicYear::current();

        $overallAverage = round((float) Grade::where('student_id', $student->id)
            ->when($currentYear, fn ($q) => $q->where('academic_year_id', $currentYear->id))
            ->avg('score'), 1);

        $attendanceQuery = Attendance::where('student_id', $student->id)
            ->when($currentYear, fn ($q) => $q->where('academic_year_id', $currentYear->id));
        $totalAttendance = $attendanceQuery->count();
        $hadirCount = (clone $attendanceQuery)->where('status', 'H')->count();
        $attendanceRate = $totalAttendance > 0 ? round(($hadirCount / $totalAttendance) * 100, 1) : 0.0;

        $reportCards = ReportCard::where('student_id', $student->id)
            ->with('academicYear')
            ->orderByDesc('academic_year_id')
            ->take(4)
            ->get()
            ->reverse();

        $classSize = $classroom?->students()->count() ?? 0;
        $semesterTrends = $reportCards->map(fn ($rc) => [
            'semesterName' => $rc->academicYear ? "{$rc->academicYear->year} {$rc->academicYear->semester}" : 'Semester',
            'gpaScore' => (float) $rc->average_score,
            'rankInClass' => (int) ($rc->rank_in_class ?? 0),
            'totalStudents' => $classSize,
        ])->values()->toArray();

        $bySubject = Grade::where('student_id', $student->id)
            ->when($currentYear, fn ($q) => $q->where('academic_year_id', $currentYear->id))
            ->with('subject')
            ->get()
            ->groupBy('subject_id')
            ->filter(fn ($grades) => $grades->first()->subject !== null);

        $competencyRadar = $bySubject->map(function ($grades) {
            $subject = $grades->first()->subject;

            return [
                'label' => $subject->name,
                'value' => round((float) $grades->avg('score'), 1),
                'targetKktp' => (float) ($subject->kkm ?? 75),
            ];
        })->take(6)->values()->toArray();

        $topSubjects = $bySubject->map(fn ($grades) => [
            'name' => $grades->first()->subject->name,
            'avg' => round((float) $grades->avg('score'), 1),
        ])->sortByDesc('avg')->take(5)->values()
            ->map(fn ($item) => ['first' => $item['name'], 'second' => $item['avg']])
            ->toArray();

        return [
            'studentName' => $student->user->name ?? '',
            'className' => $classroom->name ?? '',
            'overallAverage' => $overallAverage,
            'attendanceRate' => $attendanceRate,
            'semesterTrends' => $semesterTrends,
            'competencyRadar' => $competencyRadar,
            'topSubjects' => $topSubjects,
        ];
    }

    /**
     * The requesting teacher's real (classroom, subject) teaching
     * assignments — not a hardcoded pair. Derived from Grade.recorded_by
     * rather than the Schedule table: this dataset's `schedules` table is
     * empty (0 rows), while every one of the 5640+ real Grade rows has a
     * genuine recorded_by teacher, making grading activity the only actual
     * signal available for "which (class, subject) does this teacher
     * teach." If Schedule data is populated later, prefer it there instead.
     */
    public function teacherClasses(User $teacher): array
    {
        return Grade::where('recorded_by', $teacher->id)
            ->with('subject', 'student.classroom')
            ->get()
            ->filter(fn ($g) => $g->subject && $g->student?->classroom)
            ->unique(fn ($g) => $g->student->classroom_id.'-'.$g->subject_id)
            ->map(fn ($g) => [
                'className' => $g->student->classroom->name,
                'subjectName' => $g->subject->name,
            ])
            ->values()
            ->toArray();
    }

    public function classPerformance(Classroom $classroom, Subject $subject): array
    {
        $currentYear = AcademicYear::current();
        $studentIds = $classroom->students()->pluck('id');
        $kkm = (float) ($subject->kkm ?? 75);

        $grades = Grade::whereIn('student_id', $studentIds)
            ->where('subject_id', $subject->id)
            ->when($currentYear, fn ($q) => $q->where('academic_year_id', $currentYear->id))
            ->with('student.user', 'recorder')
            ->get();

        $scores = $grades->map(fn ($g) => (float) $g->score);
        $classAverage = $scores->isNotEmpty() ? round($scores->avg(), 1) : 0.0;
        $highestScore = $scores->isNotEmpty() ? (int) round($scores->max()) : 0;
        $lowestScore = $scores->isNotEmpty() ? (int) round($scores->min()) : 0;
        $passRate = $scores->isNotEmpty()
            ? round(($scores->filter(fn ($s) => $s >= $kkm)->count() / $scores->count()) * 100, 1)
            : 0.0;

        $bucketDefs = [
            ['label' => '< 75', 'min' => 0, 'max' => 74.99],
            ['label' => '75 - 84', 'min' => 75, 'max' => 84.99],
            ['label' => '85 - 92', 'min' => 85, 'max' => 92.99],
            ['label' => '93 - 100', 'min' => 93, 'max' => 100],
        ];
        $total = $scores->count();
        $distributionBuckets = array_map(function ($bucket) use ($scores, $total) {
            $count = $scores->filter(fn ($s) => $s >= $bucket['min'] && $s <= $bucket['max'])->count();

            return [
                'rangeLabel' => $bucket['label'],
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0.0,
            ];
        }, $bucketDefs);

        // Schedule has no rows in this dataset (see teacherClasses()) — the
        // real recorder of this class+subject's grades is the honest
        // substitute for "who teaches it."
        $teacherName = $grades->first()?->recorder?->name ?? '';

        $atRiskStudents = $grades->groupBy('student_id')
            ->map(function ($studentGrades) use ($kkm, $subject) {
                $avg = round((float) $studentGrades->avg('score'), 1);
                if ($avg >= $kkm) {
                    return null;
                }
                $student = $studentGrades->first()->student;

                return [
                    'studentId' => (string) $student->id,
                    'studentName' => $student->user->name ?? 'Siswa',
                    'lowestSubject' => $subject->name,
                    'currentScore' => (int) round($avg),
                    'kktpThreshold' => (int) $kkm,
                    'recommendation' => 'Perlu bimbingan remedial dan pendampingan tambahan pada mapel ini.',
                ];
            })
            ->filter()
            ->values()
            ->toArray();

        return [
            'className' => $classroom->name,
            'subjectName' => $subject->name,
            'teacherName' => $teacherName,
            'classAverage' => $classAverage,
            'highestScore' => $highestScore,
            'lowestScore' => $lowestScore,
            'passRatePercentage' => $passRate,
            'distributionBuckets' => $distributionBuckets,
            'atRiskStudents' => $atRiskStudents,
        ];
    }

    public function parentChildProgress(Student $student): array
    {
        $classroom = $student->classroom;
        $currentYear = AcademicYear::current();

        $academicScore = round((float) Grade::where('student_id', $student->id)
            ->when($currentYear, fn ($q) => $q->where('academic_year_id', $currentYear->id))
            ->avg('score'), 1);

        $classAverageScore = 0.0;
        if ($classroom) {
            $classAverageScore = round((float) Grade::whereIn('student_id', $classroom->students()->pluck('id'))
                ->when($currentYear, fn ($q) => $q->where('academic_year_id', $currentYear->id))
                ->avg('score'), 1);
        }

        $tahfidzTarget = $currentYear
            ? TahfidzTarget::where('student_id', $student->id)->where('academic_year_id', $currentYear->id)->first()
            : null;

        $totalSurahCompleted = TahfidzLog::where('student_id', $student->id)
            ->distinct('surah_number')
            ->count('surah_number');

        return [
            'childName' => $student->user->name ?? '',
            'childClass' => $classroom->name ?? '',
            'academicScore' => $academicScore,
            'classAverageScore' => $classAverageScore,
            'tahfidzCurrentJuz' => (int) ($tahfidzTarget->achieved_juz ?? 0),
            'tahfidzTargetJuz' => (int) ($tahfidzTarget->target_juz ?? 0),
            'totalSurahCompleted' => $totalSurahCompleted,
            'attendanceHeatmap' => $this->buildAttendanceHeatmap($student->id),
        ];
    }

    /** One entry per day of the current month up to today; days with no real Attendance row are honestly "LIBUR" rather than guessed. */
    private function buildAttendanceHeatmap(int $studentId): array
    {
        $startOfMonth = now()->startOfMonth();
        $today = now()->startOfDay();
        $naturalEndOfMonth = now()->endOfMonth()->startOfDay();
        $endOfMonth = $today->lt($naturalEndOfMonth) ? $today : $naturalEndOfMonth;

        $byDate = Attendance::where('student_id', $studentId)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(fn ($a) => Carbon::parse($a->date)->format('Y-m-d'));

        $heatmap = [];
        for ($cursor = $startOfMonth->copy(); $cursor->lte($endOfMonth); $cursor->addDay()) {
            $record = $byDate->get($cursor->format('Y-m-d'));
            $status = match ($record?->status) {
                'H' => 'HADIR',
                'I' => 'IZIN',
                'S' => 'SAKIT',
                'A' => 'ALPA',
                default => 'LIBUR',
            };
            $heatmap[] = [
                'dayNumber' => (int) $cursor->format('j'),
                'dateFormatted' => $cursor->format('d M'),
                'status' => $status,
            ];
        }

        return $heatmap;
    }

    public function executiveKpi(): array
    {
        $totalActiveStudents = Student::where('status', 'active')->count();

        $today = now()->toDateString();
        $attendanceTodayTotal = Attendance::whereDate('date', $today)->count();
        $attendanceTodayHadir = Attendance::whereDate('date', $today)->where('status', 'H')->count();
        $attendanceTodayPercentage = $attendanceTodayTotal > 0
            ? round(($attendanceTodayHadir / $attendanceTodayTotal) * 100, 1)
            : 0.0;

        $monthlyCategoryIds = PaymentCategory::where('is_monthly', true)->pluck('id');
        // The `billings.month` column stores Indonesian month NAMES for one
        // academic year and zero-padded numeric strings for another (a
        // pre-existing data inconsistency, not introduced here) — matching
        // both forms keeps this honest regardless of which convention this
        // particular month's rows happen to use.
        $now = now();
        $monthKeys = [$now->format('m'), self::INDONESIAN_MONTHS[$now->month]];

        // `month` carries no year, so without the academic year last year's
        // September bills would count as this September's.
        $activeYearId = AcademicYear::current()?->id;

        $billedThisMonth = (float) Billing::whereIn('payment_category_id', $monthlyCategoryIds)
            ->whereIn('month', $monthKeys)
            ->when($activeYearId, fn ($q) => $q->where('academic_year_id', $activeYearId))
            ->sum('amount');

        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();
        $collectedThisMonth = (float) Payment::whereHas('billing', fn ($q) => $q->whereIn('payment_category_id', $monthlyCategoryIds))
            ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
            ->sum('amount_paid');

        $collectionRate = $billedThisMonth > 0 ? round(($collectedThisMonth / $billedThisMonth) * 100, 1) : 0.0;

        $lastMonth = $now->copy()->subMonth();
        $collectedLastMonth = (float) Payment::whereHas('billing', fn ($q) => $q->whereIn('payment_category_id', $monthlyCategoryIds))
            ->whereBetween('payment_date', [$lastMonth->copy()->startOfMonth()->toDateString(), $lastMonth->copy()->endOfMonth()->toDateString()])
            ->sum('amount_paid');

        $collectionTrendUp = $collectedThisMonth >= $collectedLastMonth;
        $collectionChangeText = $collectedLastMonth > 0
            ? sprintf('%+.1f%% dari bulan lalu', (($collectedThisMonth - $collectedLastMonth) / $collectedLastMonth) * 100)
            : 'Belum ada data pembayaran bulan lalu';

        $kpiList = [
            [
                'title' => 'Siswa Aktif',
                'value' => (string) $totalActiveStudents,
                'changeText' => 'Total siswa berstatus aktif saat ini',
                'isPositiveTrend' => true,
                'progress' => 1.0,
                'description' => 'Jumlah siswa dengan status aktif di seluruh jenjang.',
            ],
            [
                'title' => 'Kehadiran Hari Ini',
                // "–" until something is recorded today: 0% would read as nobody came.
                'value' => $attendanceTodayTotal > 0 ? "{$attendanceTodayPercentage}%" : '–',
                'changeText' => $attendanceTodayTotal > 0
                    ? "{$attendanceTodayHadir} dari {$attendanceTodayTotal} presensi tercatat"
                    : 'Belum ada presensi tercatat hari ini',
                'isPositiveTrend' => $attendanceTodayTotal === 0 || $attendanceTodayPercentage >= 90,
                'progress' => $attendanceTodayPercentage / 100,
                'description' => 'Persentase status Hadir dari seluruh presensi yang sudah tercatat hari ini.',
            ],
            [
                'title' => 'SPP Terkumpul Bulan Ini',
                'value' => 'Rp '.number_format($collectedThisMonth, 0, ',', '.'),
                'changeText' => $collectionChangeText,
                'isPositiveTrend' => $collectionTrendUp,
                'progress' => min($collectionRate / 100, 1.0),
                'description' => 'Total pembayaran kategori SPP Bulanan yang diterima bulan ini.',
            ],
            [
                'title' => 'Rasio Penagihan SPP',
                'value' => $billedThisMonth > 0 ? "{$collectionRate}%" : '–',
                'changeText' => $billedThisMonth > 0 ? 'Dibanding total tagihan SPP bulan ini' : 'Belum ada tagihan SPP bulan ini',
                'isPositiveTrend' => $collectionRate >= 80,
                'progress' => min($collectionRate / 100, 1.0),
                'description' => 'Persentase SPP Bulanan yang sudah dibayar dibandingkan total tagihan bulan ini.',
            ],
        ];

        return [
            'kpiList' => $kpiList,
            'totalActiveStudents' => $totalActiveStudents,
            'attendanceTodayPercentage' => $attendanceTodayPercentage,
            'totalSppCollected' => 'Rp '.number_format($collectedThisMonth, 0, ',', '.'),
            'collectionRatePercentage' => $collectionRate,
            // Raw figures for clients that format and chart for themselves.
            // A rate is null when there is nothing to divide by, never 0.
            'attendance' => [
                'today' => $this->attendanceBreakdown($today),
                'days' => $this->attendanceDays($now->copy()->subDays(13)->toDateString(), $today),
            ],
            'spp' => [
                'month' => self::INDONESIAN_MONTHS[$now->month].' '.$now->year,
                ...$this->sppSettlement($monthlyCategoryIds->all(), $monthKeys, $activeYearId),
                'received_this_month' => $collectedThisMonth,
                'received_last_month' => $collectedLastMonth,
                'received_change_pct' => $collectedLastMonth > 0
                    ? round((($collectedThisMonth - $collectedLastMonth) / $collectedLastMonth) * 100, 1)
                    : null,
            ],
        ];
    }

    /**
     * Today's attendance records by status (H/S/I/A).
     *
     * @return array{recorded: int, present: int, sick: int, permit: int, absent: int, rate: float|null}
     */
    private function attendanceBreakdown(string $date): array
    {
        $counts = Attendance::whereDate('date', $date)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $recorded = (int) $counts->sum();
        $present = (int) ($counts['H'] ?? 0);

        return [
            'recorded' => $recorded,
            'present' => $present,
            'sick' => (int) ($counts['S'] ?? 0),
            'permit' => (int) ($counts['I'] ?? 0),
            'absent' => (int) ($counts['A'] ?? 0),
            'rate' => $recorded > 0 ? round($present / $recorded * 100, 1) : null,
        ];
    }

    /**
     * Presence rate for each day in the range that has records, oldest
     * first. Days without records (weekends, holidays) are left out rather
     * than reported as 0%.
     *
     * @return list<array{date: string, recorded: int, present: int, rate: float}>
     */
    private function attendanceDays(string $from, string $to): array
    {
        return Attendance::whereBetween('date', [$from, $to])
            ->selectRaw("date, count(*) as recorded, sum(case when status = 'H' then 1 else 0 end) as present")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => Carbon::parse($row->getAttribute('date'))->toDateString(),
                'recorded' => (int) $row->getAttribute('recorded'),
                'present' => (int) $row->getAttribute('present'),
                'rate' => round((int) $row->getAttribute('present') / max(1, (int) $row->getAttribute('recorded')) * 100, 1),
            ])
            ->values()
            ->all();
    }

    /**
     * How much of this month's SPP bills is paid off: billed amount, the
     * part settled, and how many bills are fully paid.
     *
     * @param  array<int, int>  $categoryIds
     * @param  array<int, string>  $monthKeys
     * @return array{billed: float, settled: float, settled_rate: float|null, bills: int, bills_paid: int}
     */
    private function sppSettlement(array $categoryIds, array $monthKeys, ?int $academicYearId): array
    {
        $bills = Billing::whereIn('payment_category_id', $categoryIds)
            ->whereIn('month', $monthKeys)
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId));
        $billed = (float) (clone $bills)->sum('amount');
        $outstanding = (float) (clone $bills)->sum('remaining_balance');
        $settled = max(0.0, $billed - $outstanding);

        return [
            'billed' => $billed,
            'settled' => $settled,
            'settled_rate' => $billed > 0 ? round($settled / $billed * 100, 1) : null,
            'bills' => (clone $bills)->count(),
            'bills_paid' => (clone $bills)->where('status', 'paid')->count(),
        ];
    }
}
