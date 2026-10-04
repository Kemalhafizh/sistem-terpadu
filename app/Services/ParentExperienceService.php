<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Billing;
use App\Models\DailyAssessmentScore;
use App\Models\Grade;
use App\Models\LibraryLoan;
use App\Models\MutabaahLog;
use App\Models\Student;
use App\Models\StudentPointLog;
use App\Models\TahfidzLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * What a parent sees about one child, built only from the school's records.
 * Every id here is students.id. Numbers the school has no data for are null,
 * never a placeholder: this used to return the same invented feed, digest and
 * class comparison for every child.
 *
 * Text is in the caller's language (SetLocale). Each feed event also carries a
 * `code` that never changes with the language, for the app to act on.
 */
class ParentExperienceService
{
    private const FEED_DAYS = 14;

    private const FEED_LIMIT = 30;

    private const ATTENDANCE_LABELS = ['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Tanpa keterangan'];

    private const ATTENDANCE_CODES = ['H' => 'present', 'S' => 'sick', 'I' => 'permit', 'A' => 'absent'];

    /**
     * The child's recent school events, newest first.
     *
     * `code`: present|sick|permit|absent|attendance (other status), grade, assessment,
     * reward, violation, loan, return, tahfidz.
     *
     * @return list<array{id: string, timestamp: int, title: string, type: string, code: string, description: string}>
     */
    public function getChildActivityFeed(int $childId): array
    {
        $since = Carbon::now()->subDays(self::FEED_DAYS)->startOfDay();
        $events = collect();

        foreach (Attendance::where('student_id', $childId)->where('date', '>=', $since)->get() as $a) {
            $events->push($this->event("attendance_{$a->id}", Carbon::parse($a->date)->startOfDay(), 'attendance',
                self::ATTENDANCE_CODES[$a->status] ?? 'attendance',
                __('Presensi: :status', ['status' => $this->attendanceLabel($a->status)]),
                $a->notes ?: __('Tercatat di presensi kelas.')));
        }

        foreach (Grade::with('subject:id,name')->where('student_id', $childId)->where('created_at', '>=', $since)->get() as $g) {
            $events->push($this->event("grade_{$g->id}", $g->created_at, 'academic', 'grade',
                __('Nilai :type :subject', ['type' => strtoupper((string) $g->type), 'subject' => $g->subject->name ?? __('mapel')]),
                __('Nilai :score', ['score' => $this->number($g->score)]).($g->description ? ' · '.$g->description : '')));
        }

        $scores = DailyAssessmentScore::with('dailyAssessment:id,title')
            ->where('student_id', $childId)->whereNotNull('score')->where('updated_at', '>=', $since)->get();
        foreach ($scores as $s) {
            $events->push($this->event("assessment_{$s->id}", $s->updated_at, 'academic', 'assessment',
                __('Nilai :title', ['title' => $s->dailyAssessment->title ?? __('penilaian harian')]),
                __('Nilai :score', ['score' => $this->number($s->score)])));
        }

        foreach (StudentPointLog::where('student_id', $childId)->where('created_at', '>=', $since)->get() as $p) {
            $reward = $p->type === 'reward';
            $events->push($this->event("points_{$p->id}", $p->created_at, 'discipline', $reward ? 'reward' : 'violation',
                __($reward ? 'Poin prestasi +:points' : 'Poin pelanggaran :points', ['points' => abs((int) $p->points)]),
                $p->description ?: __($reward ? 'Prestasi tercatat.' : 'Pelanggaran tercatat.')));
        }

        $loans = LibraryLoan::with('book:id,title')->where('student_id', $childId)
            ->where(fn ($q) => $q->where('borrowed_at', '>=', $since)->orWhere('returned_at', '>=', $since))->get();
        foreach ($loans as $l) {
            $title = $l->book->title ?? __('buku perpustakaan');
            if ($l->borrowed_at && Carbon::parse($l->borrowed_at)->gte($since)) {
                $events->push($this->event("loan_{$l->id}", Carbon::parse($l->borrowed_at), 'library', 'loan', __('Meminjam buku'), $title));
            }
            if ($l->returned_at && Carbon::parse($l->returned_at)->gte($since)) {
                $events->push($this->event("return_{$l->id}", Carbon::parse($l->returned_at), 'library', 'return', __('Mengembalikan buku'), $title));
            }
        }

        foreach (TahfidzLog::where('student_id', $childId)->where('date', '>=', $since)->get() as $t) {
            $surah = $t->surah_name ?? __('Surah');
            $events->push($this->event("tahfidz_{$t->id}", Carbon::parse($t->date)->startOfDay(), 'ibadah', 'tahfidz',
                __('Setoran tahfidz'),
                $t->from_ayat && $t->to_ayat
                    ? __(':surah ayat :from–:to', ['surah' => $surah, 'from' => $t->from_ayat, 'to' => $t->to_ayat])
                    : $surah));
        }

        return $events->sortByDesc('timestamp')->take(self::FEED_LIMIT)->values()->all();
    }

    /**
     * This week so far (Monday until now). A number is null when nothing was recorded.
     *
     * @return array{weekStart: int, attendancePercentage: ?float, averageGrade: ?float, ibadahScore: ?int, highlights: list<string>}
     */
    public function generateWeeklyDigest(int $childId): array
    {
        $start = Carbon::now()->startOfWeek();

        $attendance = Attendance::where('student_id', $childId)->where('date', '>=', $start->toDateString())->pluck('status');
        $present = $attendance->filter(fn ($s) => $s === 'H')->count();

        $scores = Grade::where('student_id', $childId)->where('created_at', '>=', $start)->pluck('score')
            ->merge(DailyAssessmentScore::where('student_id', $childId)->whereNotNull('score')
                ->where('updated_at', '>=', $start)->pluck('score'))
            ->map(fn ($s) => (float) $s);

        $ibadah = MutabaahLog::where('student_id', $childId)->where('date', '>=', $start->toDateString())->avg('score');
        $tahfidz = TahfidzLog::where('student_id', $childId)->where('date', '>=', $start->toDateString())->count();
        $points = StudentPointLog::where('student_id', $childId)->where('created_at', '>=', $start)->get(['type', 'points']);

        $highlights = [];
        if ($attendance->isNotEmpty()) {
            $highlights[] = __('Hadir :present dari :total presensi tercatat.', ['present' => $present, 'total' => $attendance->count()]);
            $absent = $attendance->reject(fn ($s) => $s === 'H')->countBy()
                ->map(fn ($n, $s) => $n.' '.mb_strtolower($this->attendanceLabel($s)))->values()->implode(', ');
            if ($absent !== '') {
                $highlights[] = __('Tidak hadir: :list.', ['list' => $absent]);
            }
        }
        if ($scores->isNotEmpty()) {
            $highlights[] = __(':count nilai baru masuk.', ['count' => $scores->count()]);
        }
        if ($tahfidz > 0) {
            $highlights[] = __(':count setoran tahfidz.', ['count' => $tahfidz]);
        }
        $reward = $points->where('type', 'reward')->sum('points');
        $penalty = $points->where('type', '!=', 'reward')->sum(fn ($p) => abs((int) $p->points));
        if ($reward > 0) {
            $highlights[] = __('Poin prestasi +:points.', ['points' => $reward]);
        }
        if ($penalty > 0) {
            $highlights[] = __('Poin pelanggaran :points.', ['points' => $penalty]);
        }

        return [
            'weekStart' => $start->timestamp,
            'attendancePercentage' => $attendance->isEmpty() ? null : round($present / $attendance->count() * 100, 1),
            'averageGrade' => $scores->isEmpty() ? null : round($scores->avg(), 1),
            'ibadahScore' => $ibadah === null ? null : (int) round((float) $ibadah),
            'highlights' => $highlights,
        ];
    }

    /**
     * The child's average per subject this academic year next to the class average.
     *
     * @return list<array{subject: string, childScore: float, classAverage: ?float}>
     */
    public function getChildVsClassAverage(int $childId): array
    {
        $student = Student::find($childId);
        if (! $student) {
            return [];
        }
        $year = AcademicYear::current();
        $scoped = fn ($q) => $year ? $q->where('academic_year_id', $year->id) : $q;

        $own = $scoped(Grade::with('subject:id,name')->where('student_id', $childId))->get()->groupBy('subject_id');
        if ($own->isEmpty()) {
            return [];
        }

        /** @var Collection<int|string, float> $classAverages */
        $classAverages = $student->classroom_id
            ? $scoped(Grade::whereIn('student_id', Student::where('classroom_id', $student->classroom_id)->select('id'))
                ->whereIn('subject_id', $own->keys()))
                ->groupBy('subject_id')->selectRaw('subject_id, AVG(score) as average')->pluck('average', 'subject_id')
            : collect();

        return $own->map(fn (Collection $grades, $subjectId) => [
            'subject' => $grades->first()->subject->name ?? __('Mapel'),
            'childScore' => round((float) $grades->avg('score'), 1),
            'classAverage' => isset($classAverages[$subjectId]) ? round((float) $classAverages[$subjectId], 1) : null,
        ])->sortBy('subject')->values()->all();
    }

    /**
     * Shortcuts for the parent; the bill reminder appears only when a bill is open.
     *
     * @return list<array{id: string, title: string, subtitle: string, route: string, badge?: string}>
     */
    public function getParentQuickActions(int $parentId): array
    {
        $open = Billing::whereIn('student_id', Student::where('parent_id', $parentId)->select('id'))
            ->whereIn('status', ['unpaid', 'partial'])->get(['due_date']);

        $actions = [];
        if ($open->isNotEmpty()) {
            $overdue = $open->contains(fn ($b) => $b->due_date && Carbon::parse($b->due_date)->isPast());
            $actions[] = array_filter([
                'id' => 'pay_spp',
                'title' => __('Bayar tagihan'),
                'subtitle' => __(':count tagihan belum lunas', ['count' => $open->count()]),
                'route' => 'billing',
                'badge' => $overdue ? __('Jatuh tempo') : null,
            ]);
        }
        $actions[] = ['id' => 'chat_teacher', 'title' => __('Pesan wali kelas'), 'subtitle' => __('Konsultasi belajar'), 'route' => 'conversation_list'];
        $actions[] = ['id' => 'view_rapor', 'title' => __('Rapor'), 'subtitle' => __('Capaian pembelajaran'), 'route' => 'rapor_detail'];

        return $actions;
    }

    /** @return array{id: string, timestamp: int, title: string, type: string, code: string, description: string} */
    private function event(string $id, Carbon|string $at, string $type, string $code, string $title, string $description): array
    {
        return [
            'id' => $id,
            'timestamp' => Carbon::parse($at)->timestamp,
            'title' => $title,
            'type' => $type,
            'code' => $code,
            'description' => $description,
        ];
    }

    private function attendanceLabel(string $status): string
    {
        return isset(self::ATTENDANCE_LABELS[$status]) ? __(self::ATTENDANCE_LABELS[$status]) : $status;
    }

    /** 96.5 → "96,5" in Indonesian, "96.5" otherwise; 90.0 → "90". */
    private function number(mixed $value): string
    {
        $v = (float) $value;

        return fmod($v, 1.0) === 0.0 ? (string) (int) $v : number_format($v, 1, app()->getLocale() === 'id' ? ',' : '.', '');
    }
}
