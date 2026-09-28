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
 */
class ParentExperienceService
{
    private const FEED_DAYS = 14;

    private const FEED_LIMIT = 30;

    private const ATTENDANCE_LABELS = ['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Tanpa keterangan'];

    /**
     * The child's recent school events, newest first.
     *
     * @return list<array{id: string, timestamp: int, title: string, type: string, description: string}>
     */
    public function getChildActivityFeed(int $childId): array
    {
        $since = Carbon::now()->subDays(self::FEED_DAYS)->startOfDay();
        $events = collect();

        foreach (Attendance::where('student_id', $childId)->where('date', '>=', $since)->get() as $a) {
            $events->push($this->event("attendance_{$a->id}", Carbon::parse($a->date)->startOfDay(), 'attendance',
                'Presensi: '.(self::ATTENDANCE_LABELS[$a->status] ?? $a->status),
                $a->notes ?: 'Tercatat di presensi kelas.'));
        }

        foreach (Grade::with('subject:id,name')->where('student_id', $childId)->where('created_at', '>=', $since)->get() as $g) {
            $events->push($this->event("grade_{$g->id}", $g->created_at, 'academic',
                'Nilai '.strtoupper((string) $g->type).' '.($g->subject->name ?? 'mapel'),
                'Nilai '.$this->number($g->score).($g->description ? ' · '.$g->description : '')));
        }

        $scores = DailyAssessmentScore::with('dailyAssessment:id,title')
            ->where('student_id', $childId)->whereNotNull('score')->where('updated_at', '>=', $since)->get();
        foreach ($scores as $s) {
            $events->push($this->event("assessment_{$s->id}", $s->updated_at, 'academic',
                'Nilai '.($s->dailyAssessment->title ?? 'penilaian harian'),
                'Nilai '.$this->number($s->score)));
        }

        foreach (StudentPointLog::where('student_id', $childId)->where('created_at', '>=', $since)->get() as $p) {
            $reward = $p->type === 'reward';
            $events->push($this->event("points_{$p->id}", $p->created_at, 'discipline',
                ($reward ? 'Poin prestasi +' : 'Poin pelanggaran ').abs((int) $p->points),
                $p->description ?: ($reward ? 'Prestasi tercatat.' : 'Pelanggaran tercatat.')));
        }

        $loans = LibraryLoan::with('book:id,title')->where('student_id', $childId)
            ->where(fn ($q) => $q->where('borrowed_at', '>=', $since)->orWhere('returned_at', '>=', $since))->get();
        foreach ($loans as $l) {
            $title = $l->book->title ?? 'buku perpustakaan';
            if ($l->borrowed_at && Carbon::parse($l->borrowed_at)->gte($since)) {
                $events->push($this->event("loan_{$l->id}", Carbon::parse($l->borrowed_at), 'library', 'Meminjam buku', $title));
            }
            if ($l->returned_at && Carbon::parse($l->returned_at)->gte($since)) {
                $events->push($this->event("return_{$l->id}", Carbon::parse($l->returned_at), 'library', 'Mengembalikan buku', $title));
            }
        }

        foreach (TahfidzLog::where('student_id', $childId)->where('date', '>=', $since)->get() as $t) {
            $range = $t->from_ayat && $t->to_ayat ? " ayat {$t->from_ayat}–{$t->to_ayat}" : '';
            $events->push($this->event("tahfidz_{$t->id}", Carbon::parse($t->date)->startOfDay(), 'ibadah',
                'Setoran tahfidz', trim(($t->surah_name ?? 'Surah').$range)));
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
            $highlights[] = "Hadir {$present} dari {$attendance->count()} presensi tercatat.";
            $absent = $attendance->reject(fn ($s) => $s === 'H')->countBy()
                ->map(fn ($n, $s) => $n.' '.strtolower(self::ATTENDANCE_LABELS[$s] ?? $s))->values()->implode(', ');
            if ($absent !== '') {
                $highlights[] = "Tidak hadir: {$absent}.";
            }
        }
        if ($scores->isNotEmpty()) {
            $highlights[] = "{$scores->count()} nilai baru masuk.";
        }
        if ($tahfidz > 0) {
            $highlights[] = "{$tahfidz} setoran tahfidz.";
        }
        $reward = $points->where('type', 'reward')->sum('points');
        $penalty = $points->where('type', '!=', 'reward')->sum(fn ($p) => abs((int) $p->points));
        if ($reward > 0) {
            $highlights[] = "Poin prestasi +{$reward}.";
        }
        if ($penalty > 0) {
            $highlights[] = "Poin pelanggaran {$penalty}.";
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
            'subject' => $grades->first()->subject->name ?? 'Mapel',
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
                'title' => 'Bayar tagihan',
                'subtitle' => "{$open->count()} tagihan belum lunas",
                'route' => 'billing',
                'badge' => $overdue ? 'Jatuh tempo' : null,
            ]);
        }
        $actions[] = ['id' => 'chat_teacher', 'title' => 'Pesan wali kelas', 'subtitle' => 'Konsultasi belajar', 'route' => 'conversation_list'];
        $actions[] = ['id' => 'view_rapor', 'title' => 'Rapor', 'subtitle' => 'Capaian pembelajaran', 'route' => 'rapor_detail'];

        return $actions;
    }

    /** @return array{id: string, timestamp: int, title: string, type: string, description: string} */
    private function event(string $id, Carbon|string $at, string $type, string $title, string $description): array
    {
        return [
            'id' => $id,
            'timestamp' => Carbon::parse($at)->timestamp,
            'title' => $title,
            'type' => $type,
            'description' => $description,
        ];
    }

    private function number(mixed $value): string
    {
        $v = (float) $value;

        return fmod($v, 1.0) === 0.0 ? (string) (int) $v : number_format($v, 1, ',', '');
    }
}
