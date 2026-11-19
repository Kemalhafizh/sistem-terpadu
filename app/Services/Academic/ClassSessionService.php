<?php

namespace App\Services\Academic;

use App\Models\AcademicYear;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Schedule;
use App\Models\SchedulePreset;
use App\Models\SessionAttendance;
use App\Models\Student;
use App\Models\StudentLeaveRequest;
use App\Models\TeachingJournal;
use App\Models\User;
use App\Services\Mobile\FcmNotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Sesi Kelas Hidup & Presensi Per-Mapel.
 *
 * Owns every rule of the live-lesson lifecycle so the three controllers
 * (teacher, student, admin) stay thin:
 *
 *   scheduled ──start──▶ active ──end / auto-close──▶ completed | auto_closed
 *
 * A session row is materialised (status "scheduled") the first time a
 * teacher's timetable for the day is listed, so the mobile client always has
 * a stable id/uuid to show. Starting it snapshots the classroom roster into
 * session_attendances as "alpha"; students flip themselves to hadir/telat by
 * scanning the rotating QR, and whoever never scans stays alpha when the
 * session closes.
 */
class ClassSessionService
{
    public const DAY_NAMES = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    /** Transitions an admin correction may make (blueprint 77.6.3). */
    private const OVERRIDE_TRANSITIONS = [
        SessionAttendance::STATUS_ALPHA => [SessionAttendance::STATUS_HADIR, SessionAttendance::STATUS_SAKIT, SessionAttendance::STATUS_IZIN],
        SessionAttendance::STATUS_SAKIT => [SessionAttendance::STATUS_HADIR],
        SessionAttendance::STATUS_IZIN => [SessionAttendance::STATUS_HADIR],
        SessionAttendance::STATUS_TELAT => [SessionAttendance::STATUS_HADIR],
    ];

    /** @var array<int, array{start: string, end: string}>|null */
    private ?array $lessonTimes = null;

    public function __construct(private FcmNotificationService $fcm) {}

    // ─────────────────────────────────────────────────────────────────────
    // Timetable
    // ─────────────────────────────────────────────────────────────────────

    public static function dayName(Carbon $date): string
    {
        return self::DAY_NAMES[$date->dayOfWeekIso];
    }

    /**
     * Clock times of every lesson period ("jam ke-N") in the active preset.
     *
     * @return array<int, array{start: string, end: string}>
     */
    public function lessonTimes(): array
    {
        if ($this->lessonTimes !== null) {
            return $this->lessonTimes;
        }

        $preset = SchedulePreset::where('is_active', true)->with('times')->first();

        return $this->lessonTimes = $preset
            ? $preset->times->mapWithKeys(fn ($t) => [
                (int) $t->session_number => [
                    'start' => Carbon::parse($t->start_time)->format('H:i:s'),
                    'end' => Carbon::parse($t->end_time)->format('H:i:s'),
                ],
            ])->all()
            : [];
    }

    /**
     * Start of the schedule's first period and end of its last one, or null
     * when the active preset doesn't define those periods.
     *
     * @return array{start: string, end: string}|null
     */
    public function resolveTimes(Schedule $schedule): ?array
    {
        $times = $this->lessonTimes();
        $first = $times[(int) $schedule->session_start] ?? null;
        $last = $times[(int) $schedule->session_end] ?? null;

        return $first && $last ? ['start' => $first['start'], 'end' => $last['end']] : null;
    }

    /**
     * The session row for a schedule on a date, created as "scheduled" if it
     * doesn't exist yet. Safe against two devices listing at the same time.
     */
    public function materialize(Schedule $schedule, string $date): ?ClassSession
    {
        $times = $this->resolveTimes($schedule);
        if (! $times) {
            return null;
        }

        $attributes = [
            'teacher_id' => $schedule->teacher_id,
            'classroom_id' => $schedule->classroom_id,
            'subject_id' => $schedule->subject_id,
            'academic_year_id' => $schedule->academic_year_id,
            'jam_ke' => (int) $schedule->session_start,
            'scheduled_start' => $times['start'],
            'scheduled_end' => $times['end'],
            'status' => ClassSession::STATUS_SCHEDULED,
        ];

        $key = ['schedule_id' => $schedule->id, 'session_date' => $date];

        try {
            $session = ClassSession::firstOrCreate($key, $attributes);
        } catch (UniqueConstraintViolationException) {
            $session = ClassSession::where($key)->firstOrFail();
        }

        // Keep a not-yet-started session in step with timetable edits.
        if ($session->status === ClassSession::STATUS_SCHEDULED) {
            $session->fill($attributes);
            if ($session->isDirty()) {
                $session->save();
            }
        }

        return $session;
    }

    /**
     * Today's lessons for a teacher (blueprint 117.4 `today`), materialising
     * one session per timetable entry.
     *
     * @return Collection<int, ClassSession>
     */
    public function todaySessionsForTeacher(User $teacher, ?Carbon $now = null): Collection
    {
        $now ??= now();
        $date = $now->toDateString();

        $schedules = Schedule::where('teacher_id', $teacher->id)
            ->where('day', self::dayName($now))
            ->when(AcademicYear::current(), fn ($q, $year) => $q->where('academic_year_id', $year->id))
            ->get();

        foreach ($schedules as $schedule) {
            $this->materialize($schedule, $date);
        }

        return $this->withListData(ClassSession::forTeacher($teacher->id)->forDate($date))
            ->orderBy('scheduled_start')
            ->get();
    }

    // ─────────────────────────────────────────────────────────────────────
    // Lifecycle
    // ─────────────────────────────────────────────────────────────────────

    /**
     * "Mulai Kelas" (blueprint 117.4 `start`, rules 1–3 and 6).
     */
    public function start(User $teacher, int $scheduleId, ?string $topic = null, ?Carbon $now = null): ClassSession
    {
        $now ??= now();

        $schedule = Schedule::find($scheduleId);
        if (! $schedule) {
            throw new ClassSessionException(__('Jadwal tidak ditemukan.'), 404, errorCode: 'schedule_not_found');
        }
        if ((int) $schedule->teacher_id !== (int) $teacher->id) {
            throw new ClassSessionException(__('Anda tidak dijadwalkan mengajar pada jadwal ini.'), 403, errorCode: 'not_your_schedule');
        }
        if ($schedule->day !== self::dayName($now)) {
            throw new ClassSessionException(__('Jadwal ini untuk hari :day, bukan hari ini.', ['day' => __((string) $schedule->day)]), 422, errorCode: 'outside_schedule_window');
        }
        $currentYear = AcademicYear::current();
        if ($currentYear && (int) $schedule->academic_year_id !== (int) $currentYear->id) {
            throw new ClassSessionException(__('Jadwal ini bukan milik tahun ajaran aktif.'), 422, errorCode: 'schedule_not_current_year');
        }

        $session = $this->materialize($schedule, $now->toDateString());
        if (! $session) {
            throw new ClassSessionException(__('Jam pelajaran untuk jadwal ini belum dikonfigurasi. Hubungi Waka Kurikulum.'), 422, errorCode: 'lesson_hours_missing');
        }

        $session = DB::transaction(function () use ($session, $topic, $now) {
            /** @var ClassSession $session */
            $session = ClassSession::whereKey($session->id)->lockForUpdate()->firstOrFail();

            if ($session->isActive()) {
                throw new ClassSessionException(__('Sesi kelas ini sudah dimulai.'), 409, ['session_id' => $session->id], 'session_already_started');
            }
            if ($session->status !== ClassSession::STATUS_SCHEDULED) {
                throw new ClassSessionException(__('Sesi kelas ini sudah berakhir hari ini.'), 409, ['session_id' => $session->id], 'session_already_ended');
            }
            if ($now->lt($session->startableFrom())) {
                throw new ClassSessionException(__('Kelas baru bisa dimulai pukul :time.', ['time' => $session->startableFrom()->format('H:i')]), 422, errorCode: 'outside_schedule_window');
            }
            if ($now->gt($session->scheduledEndAt())) {
                throw new ClassSessionException(__('Waktu jadwal kelas ini sudah lewat.'), 422, errorCode: 'outside_schedule_window');
            }

            $session->update([
                'status' => ClassSession::STATUS_ACTIVE,
                'actual_start' => $now,
                'qr_secret' => Str::random(64),
                'topic' => $topic !== null && trim($topic) !== '' ? trim($topic) : $session->topic,
            ]);

            $this->populateRoster($session, $now);

            return $session;
        });

        $this->notifyStudentsSessionStarted($session);

        return $this->loadDetail($session);
    }

    /**
     * Snapshot the classroom roster as "alpha" (rule 6): anyone who doesn't
     * check in before the session closes stays alpha. Approved student leaves
     * (C3) or daily S/I entries are pre-marked as "sakit"/"izin" automatically.
     */
    private function populateRoster(ClassSession $session, Carbon $now): void
    {
        $studentIds = Student::where('classroom_id', $session->classroom_id)
            ->where('status', 'active')
            ->pluck('id');

        if ($studentIds->isEmpty()) {
            return;
        }

        $sessionDate = (string) $session->session_date;

        $approvedLeaves = StudentLeaveRequest::query()
            ->whereIn('student_id', $studentIds)
            ->whereIn('status', StudentLeaveRequest::APPROVED_STATUSES)
            ->whereDate('start_date', '<=', $sessionDate)
            ->whereDate('end_date', '>=', $sessionDate)
            ->get()
            ->keyBy('student_id');

        $dailyExcused = Attendance::query()
            ->whereIn('student_id', $studentIds)
            ->where('date', $sessionDate)
            ->whereNull('schedule_id')
            ->whereIn('status', ['S', 'I'])
            ->get()
            ->keyBy('student_id');

        $rows = $studentIds->map(function ($studentId) use ($session, $now, $approvedLeaves, $dailyExcused) {
            /** @var StudentLeaveRequest|null $leave */
            $leave = $approvedLeaves->get($studentId);
            /** @var Attendance|null $daily */
            $daily = $dailyExcused->get($studentId);

            if ($leave) {
                $status = $leave->sessionAttendanceStatus();
                $method = SessionAttendance::METHOD_MANUAL_TEACHER;
                $recordedBy = $leave->decided_by ?? $leave->submitted_by;
                $leaveRequestId = $leave->id;
                $notes = 'Izin disetujui ('.strtoupper($leave->type).'): '.$leave->reason;
            } elseif ($daily) {
                $status = $daily->status === 'S' ? SessionAttendance::STATUS_SAKIT : SessionAttendance::STATUS_IZIN;
                $method = SessionAttendance::METHOD_MANUAL_TEACHER;
                $recordedBy = $daily->recorded_by;
                $leaveRequestId = $daily->student_leave_request_id;
                $notes = $daily->notes ?: ($daily->status === 'S' ? 'Sakit (Presensi Harian)' : 'Izin (Presensi Harian)');
            } else {
                $status = SessionAttendance::STATUS_ALPHA;
                $method = SessionAttendance::METHOD_AUTO_ALPHA;
                $recordedBy = null;
                $leaveRequestId = null;
                $notes = null;
            }

            return [
                'class_session_id' => $session->id,
                'student_id' => $studentId,
                'status' => $status,
                'check_in_method' => $method,
                'recorded_by' => $recordedBy,
                'student_leave_request_id' => $leaveRequestId,
                'notes' => $notes,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        });

        foreach ($rows->chunk(500) as $chunk) {
            SessionAttendance::insertOrIgnore($chunk->values()->all());
        }
    }

    /**
     * "Akhiri Kelas" by the teacher.
     */
    public function end(User $teacher, int $sessionId, ?string $notes = null, ?string $topic = null): ClassSession
    {
        $session = $this->findForTeacher($teacher, $sessionId);

        if ($session->status === ClassSession::STATUS_SCHEDULED) {
            throw new ClassSessionException(__('Sesi kelas belum dimulai.'), 409, errorCode: 'session_not_started');
        }
        if (! $session->isActive()) {
            throw new ClassSessionException(__('Sesi kelas sudah berakhir.'), 409, errorCode: 'session_ended');
        }

        return $this->close($session, false, $notes, $topic);
    }

    /**
     * Close an active session (teacher "end" or scheduler auto-close).
     * Students still "alpha" stay alpha — that is final (rule 6).
     */
    public function close(ClassSession $session, bool $auto, ?string $notes = null, ?string $topic = null, ?Carbon $now = null): ClassSession
    {
        $now ??= now();

        $closed = DB::transaction(function () use ($session, $auto, $notes, $topic, $now) {
            /** @var ClassSession $locked */
            $locked = ClassSession::whereKey($session->id)->lockForUpdate()->firstOrFail();
            if (! $locked->isActive()) {
                return null; // closed concurrently (teacher vs scheduler)
            }

            $locked->update([
                'status' => $auto ? ClassSession::STATUS_AUTO_CLOSED : ClassSession::STATUS_COMPLETED,
                'actual_end' => $now,
                'is_auto_closed' => $auto,
                'qr_secret' => null, // invalidates the QR immediately
                'notes' => $notes !== null && trim($notes) !== '' ? trim($notes) : $locked->notes,
                'topic' => $topic !== null && trim($topic) !== '' ? trim($topic) : $locked->topic,
            ]);

            return $locked;
        });

        if (! $closed) {
            throw new ClassSessionException(__('Sesi kelas sudah berakhir.'), 409, errorCode: 'session_ended');
        }

        $closed = $this->loadDetail($closed);

        $this->createJournalDraft($closed);
        if ($auto) {
            $this->audit(null, 'class_session.auto_closed', $closed, null, [
                'message' => "Sesi kelas {$closed->subject?->name} di {$closed->classroom?->name} ditutup otomatis oleh sistem.",
                'alpha_count' => $closed->absent_count,
            ]);
        }
        $this->notifySessionClosed($closed, $auto);

        return $closed;
    }

    /**
     * Blueprint 117.5: close every active session whose scheduled end plus
     * the grace period has passed. Returns how many were closed.
     */
    public function autoCloseExpired(?Carbon $now = null): int
    {
        $now ??= now();
        $closed = 0;

        // get(), not each(): closing flips rows out of the "active" filter, and
        // offset-based chunking would then skip every other expired session.
        $candidates = ClassSession::active()
            ->where('session_date', '<=', $now->toDateString())
            ->orderBy('id')
            ->get();

        foreach ($candidates as $session) {
            if ($now->lte($session->autoCloseAt())) {
                continue;
            }
            try {
                $this->close($session, true, now: $now);
                $closed++;
            } catch (ClassSessionException) {
                // Closed by the teacher between the query and the lock.
            }
        }

        return $closed;
    }

    /**
     * Optional integration (77.10): a lesson that ended with a topic becomes
     * a draft TeachingJournal the teacher can complete and submit.
     */
    private function createJournalDraft(ClassSession $session): void
    {
        if (! config('class_sessions.create_teaching_journal_draft', true)
            || $session->teaching_journal_id
            || blank($session->topic)) {
            return;
        }

        try {
            $journal = TeachingJournal::create([
                'user_id' => $session->teacher_id,
                'subject_id' => $session->subject_id,
                'classroom_id' => $session->classroom_id,
                'academic_year_id' => $session->academic_year_id,
                'teaching_date' => $session->session_date,
                'jam_ke' => $session->jam_ke,
                'topic' => Str::limit($session->topic, 250, ''),
                'notes' => $session->notes,
                'students_present' => $session->present_count,
                'students_absent' => $session->total_students - $session->present_count,
                'status' => 'draft',
            ]);
            $session->forceFill(['teaching_journal_id' => $journal->id])->saveQuietly();
        } catch (Throwable $e) {
            // A journal is a convenience; never fail closing a lesson over it.
            Log::warning('ClassSession: teaching journal draft failed', ['session_id' => $session->id, 'error' => $e->getMessage()]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Rotating QR (rule 4)
    // ─────────────────────────────────────────────────────────────────────

    private function qrWindow(Carbon $now): int
    {
        return intdiv($now->getTimestamp(), max(5, (int) config('class_sessions.qr_rotation_seconds', 30)));
    }

    /**
     * The QR payload for a window: "<session uuid>.<HMAC of uuid+window>".
     * Deterministic per window, so every teacher device shows the same code
     * and polling never writes; unforgeable without the per-session secret,
     * which is dropped when the session closes.
     */
    public function qrTokenFor(ClassSession $session, int $window): string
    {
        $signature = substr(hash_hmac('sha256', $session->uuid.'|'.$window, (string) $session->qr_secret), 0, 32);

        return $session->uuid.'.'.$signature;
    }

    /**
     * @return array{qr_token: string, expires_at: string, session_uuid: string, remaining_seconds: int, rotation_seconds: int}
     */
    public function currentQr(User $teacher, int $sessionId, ?Carbon $now = null): array
    {
        $now ??= now();
        $session = $this->findForTeacher($teacher, $sessionId);

        if (! $session->isActive() || ! $session->qr_secret) {
            throw new ClassSessionException(__('QR hanya tersedia saat sesi kelas aktif.'), 409, errorCode: 'qr_not_active');
        }

        $rotation = max(5, (int) config('class_sessions.qr_rotation_seconds', 30));
        $window = $this->qrWindow($now);
        $expiresAt = Carbon::createFromTimestamp(($window + 1) * $rotation, $now->getTimezone());

        return [
            'qr_token' => $this->qrTokenFor($session, $window),
            'expires_at' => $expiresAt->toIso8601String(),
            'session_uuid' => $session->uuid,
            'remaining_seconds' => max(0, (int) $now->diffInSeconds($expiresAt, false)),
            'rotation_seconds' => $rotation,
        ];
    }

    private function qrTokenIsValid(ClassSession $session, string $token, Carbon $now): bool
    {
        if (! $session->qr_secret) {
            return false;
        }

        $window = $this->qrWindow($now);
        $grace = max(0, (int) config('class_sessions.qr_grace_windows', 1));
        for ($w = $window; $w >= $window - $grace; $w--) {
            if (hash_equals($this->qrTokenFor($session, $w), $token)) {
                return true;
            }
        }

        return false;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Student self check-in (rules 5 and 11)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * @return array{success: bool, message: string, status: string, session_id: int, session_subject: ?string, session_class: ?string, checked_in_at: ?string}
     */
    public function scan(User $user, string $token, ?Carbon $now = null): array
    {
        $now ??= now();
        $student = $this->studentFor($user);

        $token = trim($token);
        [$uuid] = explode('.', $token, 2) + [null];
        $session = $uuid && Str::isUuid($uuid)
            ? ClassSession::with(['subject', 'classroom'])->where('uuid', $uuid)->first()
            : null;
        if (! $session) {
            throw new ClassSessionException(__('QR tidak valid. Pastikan Anda memindai QR sesi kelas dari layar guru.'), 422, errorCode: 'qr_invalid');
        }

        if ($session->hasEnded()) {
            throw new ClassSessionException(__('Sesi kelas sudah berakhir.'), 409, errorCode: 'session_ended');
        }
        if (! $session->isActive()) {
            throw new ClassSessionException(__('Sesi kelas belum dimulai.'), 409, errorCode: 'session_not_started');
        }
        if (! $this->qrTokenIsValid($session, $token, $now)) {
            throw new ClassSessionException(__('QR token sudah kedaluwarsa'), 422, errorCode: 'qr_expired');
        }
        if ((int) $student->classroom_id !== (int) $session->classroom_id) {
            throw new ClassSessionException(__('Anda tidak terdaftar di kelas ini'), 403, errorCode: 'not_enrolled');
        }

        $attendance = DB::transaction(function () use ($session, $student, $user, $now) {
            $attendance = SessionAttendance::where('class_session_id', $session->id)
                ->where('student_id', $student->id)
                ->lockForUpdate()
                ->first();

            if ($attendance && $attendance->isPresent()) {
                throw new ClassSessionException(__('Anda sudah tercatat hadir di sesi ini'), 409, [
                    'status' => $attendance->status,
                    'checked_in_at' => $attendance->checked_in_at?->toIso8601String(),
                ], 'already_checked_in');
            }

            $lateAfter = $session->scheduledStartAt()->addMinutes((int) config('class_sessions.late_after_minutes', 15));
            $values = [
                'status' => $now->gt($lateAfter) ? SessionAttendance::STATUS_TELAT : SessionAttendance::STATUS_HADIR,
                'check_in_method' => SessionAttendance::METHOD_QR_SCAN,
                'checked_in_at' => $now,
                'recorded_by' => $user->id,
                'student_leave_request_id' => null,
            ];

            // A student moved into the class after the session started has no roster row yet.
            return $attendance
                ? tap($attendance)->update($values)
                : SessionAttendance::create($values + ['class_session_id' => $session->id, 'student_id' => $student->id]);
        });

        $late = $attendance->status === SessionAttendance::STATUS_TELAT;

        return [
            'success' => true,
            'message' => $late ? 'Presensi berhasil dicatat (terlambat)' : 'Presensi berhasil dicatat',
            'status' => $attendance->status,
            'session_id' => $session->id,
            'session_subject' => $session->subject?->name,
            'session_class' => $session->classroom?->name,
            'checked_in_at' => $attendance->checked_in_at?->toIso8601String(),
        ];
    }

    /**
     * The student's class session in progress right now, if any.
     *
     * @return array{session_id: int, session_uuid: string, subject_name: ?string, classroom_name: ?string, teacher_name: ?string, scheduled_start: string, scheduled_end: string, attendance_status: ?string, can_scan_qr: bool}|null
     */
    public function activeSessionForStudent(User $user): ?array
    {
        $student = $this->studentFor($user);

        $session = ClassSession::active()
            ->where('classroom_id', $student->classroom_id)
            ->forToday()
            ->with(['subject', 'classroom', 'teacher'])
            ->latest('actual_start')
            ->first();

        if (! $session) {
            return null;
        }

        $attendance = SessionAttendance::where('class_session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();

        return [
            'session_id' => $session->id,
            'session_uuid' => $session->uuid,
            'subject_name' => $session->subject?->name,
            'classroom_name' => $session->classroom?->name,
            'teacher_name' => $session->teacher?->name,
            'scheduled_start' => substr($session->scheduled_start, 0, 5),
            'scheduled_end' => substr($session->scheduled_end, 0, 5),
            'attendance_status' => $attendance?->status,
            'can_scan_qr' => ! $attendance?->isPresent(),
        ];
    }

    /**
     * One student's lessons on [date] and their attendance in each, for the
     * student themself (studentFor) or a parent who has already been checked
     * to own the child. Lists the sessions of the student's class that day,
     * plus any other session the student has a record in (a class change).
     * Cancelled sessions are left out. A lesson nobody has recorded yet has
     * `status_code: null`; `awaiting_scan` marks the automatic "alpha" a
     * running lesson starts with, which the student can still turn into
     * "hadir" by scanning.
     *
     * @return array{date: string, sessions: list<array<string, mixed>>, summary: array<string, int>}
     */
    public function dayAttendanceFor(Student $student, string $date): array
    {
        $records = SessionAttendance::where('student_id', $student->id)
            ->whereHas('classSession', fn ($q) => $q->forDate($date))
            ->get()
            ->keyBy('class_session_id');

        $sessions = ClassSession::forDate($date)
            ->where('status', '!=', ClassSession::STATUS_CANCELLED)
            ->where(fn ($q) => $q->where('classroom_id', $student->classroom_id)->orWhereIn('id', $records->keys()))
            ->with(['subject', 'teacher'])
            ->orderBy('scheduled_start')
            ->get();

        $rows = $sessions->map(function (ClassSession $session) use ($records) {
            /** @var SessionAttendance|null $record */
            $record = $records->get($session->id);

            return [
                'session_id' => $session->id,
                'subject_name' => $session->subject?->name,
                'teacher_name' => $session->teacher?->name,
                'jam_ke' => $session->jam_ke,
                'scheduled_start' => substr((string) $session->scheduled_start, 0, 5),
                'scheduled_end' => substr((string) $session->scheduled_end, 0, 5),
                'session_status' => $session->status,
                'status_code' => $record?->status,
                'status' => $record ? $this->statusLabel($record->status) : null,
                'check_in_method' => $record?->check_in_method,
                'checked_in_at' => $record?->checked_in_at?->toIso8601String(),
                'awaiting_scan' => $record !== null
                    && $session->status === ClassSession::STATUS_ACTIVE
                    && $record->check_in_method === SessionAttendance::METHOD_AUTO_ALPHA,
            ];
        })->values();

        $recorded = $rows->filter(fn (array $row) => $row['status_code'] !== null && ! $row['awaiting_scan']);
        $summary = ['sessions' => $rows->count(), 'recorded' => $recorded->count()];
        foreach (SessionAttendance::STATUSES as $status) {
            $summary[$status] = $recorded->where('status_code', $status)->count();
        }

        return ['date' => $date, 'sessions' => $rows->all(), 'summary' => $summary];
    }

    /** [dayAttendanceFor] for the signed-in student. */
    public function dayAttendanceForUser(User $user, string $date): array
    {
        return $this->dayAttendanceFor($this->studentFor($user), $date);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Teacher manual attendance (rule 7)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * @return Collection<int, SessionAttendance>
     */
    public function roster(User $teacher, int $sessionId): Collection
    {
        return $this->rosterOf($this->findForTeacher($teacher, $sessionId));
    }

    /**
     * @return Collection<int, SessionAttendance>
     */
    public function rosterOf(ClassSession $session): Collection
    {
        return $session->attendances()
            ->with(['student.user', 'overrider'])
            ->get()
            ->sortBy(fn (SessionAttendance $a) => mb_strtolower((string) $a->student?->user?->name))
            ->values();
    }

    public function markManual(User $teacher, int $sessionId, int $studentId, string $status, ?string $notes = null): SessionAttendance
    {
        $session = $this->findForTeacher($teacher, $sessionId);
        $this->assertManualEditable($session);

        return DB::transaction(fn () => $this->applyManual($session, $teacher, $studentId, $status, $notes))
            ->load(['student.user', 'overrider']);
    }

    /**
     * @param  array<int, array{student_id: int, status: string, notes?: ?string}>  $items
     * @return array{updated: int, failed: int, errors: array<int, string>}
     */
    public function markManualBulk(User $teacher, int $sessionId, array $items): array
    {
        $session = $this->findForTeacher($teacher, $sessionId);
        $this->assertManualEditable($session);

        $updated = 0;
        $errors = [];

        DB::transaction(function () use ($session, $teacher, $items, &$updated, &$errors) {
            foreach ($items as $item) {
                try {
                    $this->applyManual($session, $teacher, (int) $item['student_id'], (string) $item['status'], $item['notes'] ?? null);
                    $updated++;
                } catch (ClassSessionException $e) {
                    $errors[] = "Siswa #{$item['student_id']}: {$e->getMessage()}";
                }
            }
        });

        return ['updated' => $updated, 'failed' => count($errors), 'errors' => $errors];
    }

    private function assertManualEditable(ClassSession $session): void
    {
        if (! $session->isActive()) {
            throw new ClassSessionException(__('Absensi manual hanya bisa dilakukan saat sesi kelas aktif. Untuk koreksi setelah sesi berakhir, hubungi Waka Kurikulum/TU.'), 409, errorCode: 'manual_not_active');
        }
    }

    private function applyManual(ClassSession $session, User $teacher, int $studentId, string $status, ?string $notes): SessionAttendance
    {
        if (! in_array($status, SessionAttendance::STATUSES, true)) {
            throw new ClassSessionException(__('Status :status tidak dikenal.', ['status' => $status]), 422, errorCode: 'unknown_status');
        }

        $attendance = SessionAttendance::where('class_session_id', $session->id)
            ->where('student_id', $studentId)
            ->lockForUpdate()
            ->first();

        if (! $attendance) {
            $inClass = Student::whereKey($studentId)->where('classroom_id', $session->classroom_id)->exists();
            if (! $inClass) {
                throw new ClassSessionException(__('Siswa tidak terdaftar di kelas sesi ini.'), 404, errorCode: 'student_not_in_session');
            }
            $attendance = new SessionAttendance(['class_session_id' => $session->id, 'student_id' => $studentId]);
        }

        $present = in_array($status, SessionAttendance::PRESENT_STATUSES, true);
        $attendance->fill([
            'status' => $status,
            'check_in_method' => SessionAttendance::METHOD_MANUAL_TEACHER,
            'checked_in_at' => $present ? ($attendance->checked_in_at ?? now()) : null,
            'recorded_by' => $teacher->id,
            'student_leave_request_id' => null,
            'notes' => $notes !== null ? (trim($notes) ?: null) : $attendance->notes,
        ])->save();

        return $attendance;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Admin / Waka Kurikulum / TU (rules 8–9)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * @param  array{date?: ?string, teacher_id?: ?int, classroom_id?: ?int, subject_id?: ?int, status?: ?string}  $filters
     */
    public function paginateSessions(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = ClassSession::query()
            ->when($filters['date'] ?? null, fn ($q, $date) => $q->forDate($date))
            ->when($filters['teacher_id'] ?? null, fn ($q, $id) => $q->where('teacher_id', $id))
            ->when($filters['classroom_id'] ?? null, fn ($q, $id) => $q->where('classroom_id', $id))
            ->when($filters['subject_id'] ?? null, fn ($q, $id) => $q->where('subject_id', $id))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status));

        return $this->withListData($query)
            ->orderByDesc('session_date')
            ->orderBy('scheduled_start')
            ->orderBy('id')
            ->paginate(max(1, min(100, $perPage)));
    }

    public function findSession(int $sessionId): ClassSession
    {
        $session = ClassSession::find($sessionId);
        if (! $session) {
            throw new ClassSessionException(__('Sesi kelas tidak ditemukan.'), 404, errorCode: 'session_not_found');
        }

        return $session;
    }

    /**
     * Correct a student's attendance with a mandatory reason. Presence that
     * was recorded can't be taken away ("hadir" → anything is refused), so a
     * correction can only ever grant attendance or an excuse (77.6.3).
     */
    public function override(User $admin, int $attendanceId, string $status, string $reason, ?string $ip = null): SessionAttendance
    {
        return DB::transaction(function () use ($admin, $attendanceId, $status, $reason, $ip) {
            $attendance = SessionAttendance::whereKey($attendanceId)->lockForUpdate()->first();
            if (! $attendance) {
                throw new ClassSessionException(__('Data presensi tidak ditemukan.'), 404, errorCode: 'attendance_not_found');
            }

            $session = $attendance->classSession;
            if (! $session || ! in_array($session->status, ClassSession::HELD_STATUSES, true)) {
                throw new ClassSessionException(__('Presensi hanya bisa dikoreksi untuk sesi yang sudah dilaksanakan.'), 409, errorCode: 'session_not_held');
            }

            $from = $attendance->status;
            if ($from === $status) {
                throw new ClassSessionException(__('Status presensi sudah :status.', ['status' => $this->statusLabel($status)]), 422, errorCode: 'status_unchanged');
            }
            $allowed = self::OVERRIDE_TRANSITIONS[$from] ?? [];
            if (! in_array($status, $allowed, true)) {
                throw new ClassSessionException(
                    $allowed
                        ? __('Koreksi dari :from hanya boleh ke: :to.', [
                            'from' => $this->statusLabel($from),
                            'to' => implode(', ', array_map(fn ($s) => $this->statusLabel($s), $allowed)),
                        ])
                        : __('Presensi berstatus :from tidak dapat dikoreksi. Kehadiran yang sudah tercatat tidak boleh dihapus.', ['from' => $this->statusLabel($from)]),
                    422,
                    errorCode: 'transition_not_allowed',
                );
            }

            $attendance->update([
                'status' => $status,
                'is_override' => true,
                'override_by' => $admin->id,
                'override_at' => now(),
                'override_reason' => trim($reason),
            ]);

            $this->audit($admin, 'session_attendance.override', $attendance, ['status' => $from], [
                'status' => $status,
                'reason' => trim($reason),
                'class_session_id' => $session->id,
                'student_id' => $attendance->student_id,
            ], $ip);

            return $attendance->load(['student.user', 'overrider']);
        });
    }

    /**
     * Attendance recap over held sessions in a date range, grouped by
     * student, class, or subject.
     *
     * @return array{summary: array<string, int|float>, details: array<int, array<string, mixed>>}
     */
    public function report(string $startDate, string $endDate, ?int $classroomId, ?int $subjectId, string $groupBy): array
    {
        $base = DB::table('session_attendances as sa')
            ->join('class_sessions as cs', 'cs.id', '=', 'sa.class_session_id')
            ->whereNull('cs.deleted_at')
            ->whereIn('cs.status', ClassSession::HELD_STATUSES)
            ->whereBetween('cs.session_date', [$startDate, $endDate])
            ->when($classroomId, fn ($q, $id) => $q->where('cs.classroom_id', $id))
            ->when($subjectId, fn ($q, $id) => $q->where('cs.subject_id', $id));

        $counts = implode(', ', array_map(
            fn ($s) => "SUM(CASE WHEN sa.status = '{$s}' THEN 1 ELSE 0 END) as {$s}",
            SessionAttendance::STATUSES
        ));

        $totals = (clone $base)->selectRaw("COUNT(DISTINCT cs.id) as total_sessions, COUNT(*) as total_records, {$counts}")->first();

        $details = (clone $base);
        switch ($groupBy) {
            case 'class':
                $details->join('classrooms as c', 'c.id', '=', 'cs.classroom_id')
                    ->selectRaw("cs.classroom_id as group_id, NULL as student_name, c.name as classroom_name, NULL as subject_name, {$counts}")
                    ->groupBy('cs.classroom_id', 'c.name')
                    ->orderBy('c.name');
                break;
            case 'subject':
                $details->join('subjects as sub', 'sub.id', '=', 'cs.subject_id')
                    ->selectRaw("cs.subject_id as group_id, NULL as student_name, NULL as classroom_name, sub.name as subject_name, {$counts}")
                    ->groupBy('cs.subject_id', 'sub.name')
                    ->orderBy('sub.name');
                break;
            default: // student
                $details->join('students as st', 'st.id', '=', 'sa.student_id')
                    ->leftJoin('users as u', 'u.id', '=', 'st.user_id')
                    ->leftJoin('classrooms as c', 'c.id', '=', 'st.classroom_id')
                    ->selectRaw("sa.student_id as group_id, st.nis as student_nis, u.name as student_name, c.name as classroom_name, NULL as subject_name, {$counts}")
                    ->groupBy('sa.student_id', 'st.nis', 'u.name', 'c.name')
                    ->orderBy('u.name');
        }

        $rows = $details->get()->map(function ($row) use ($groupBy) {
            $hadir = (int) $row->hadir;
            $telat = (int) $row->telat;
            $total = $hadir + $telat + (int) $row->alpha + (int) $row->sakit + (int) $row->izin;

            // Every key is always present (null when not applicable to the grouping).
            return [
                'group_id' => (int) $row->group_id,
                'student_nis' => $groupBy === 'student' ? $row->student_nis : null,
                'student_name' => $row->student_name,
                'classroom_name' => $row->classroom_name,
                'subject_name' => $row->subject_name,
                'hadir' => $hadir,
                'telat' => $telat,
                'alpha' => (int) $row->alpha,
                'sakit' => (int) $row->sakit,
                'izin' => (int) $row->izin,
                'total' => $total,
                'presence_rate' => $total > 0 ? round(($hadir + $telat) / $total, 4) : 0.0,
            ];
        })->values()->all();

        $totalRecords = (int) ($totals->total_records ?? 0);
        $present = (int) ($totals->hadir ?? 0) + (int) ($totals->telat ?? 0);

        return [
            'summary' => [
                'total_sessions' => (int) ($totals->total_sessions ?? 0),
                'total_records' => $totalRecords,
                'average_presence_rate' => $totalRecords > 0 ? round($present / $totalRecords, 4) : 0.0,
                'total_hadir' => (int) ($totals->hadir ?? 0),
                'total_telat' => (int) ($totals->telat ?? 0),
                'total_alpha' => (int) ($totals->alpha ?? 0),
                'total_sakit' => (int) ($totals->sakit ?? 0),
                'total_izin' => (int) ($totals->izin ?? 0),
            ],
            'details' => $rows,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────

    /**
     * A session the user teaches. Admin/superadmin may act on any session
     * except starting one (rule 2 is checked against the schedule itself).
     */
    public function findForTeacher(User $teacher, int $sessionId): ClassSession
    {
        $session = $this->findSession($sessionId);

        if ((int) $session->teacher_id !== (int) $teacher->id && ! in_array($teacher->role, ['admin', 'superadmin'], true)) {
            throw new ClassSessionException(__('Anda bukan guru pengampu sesi kelas ini.'), 403, errorCode: 'not_your_schedule');
        }

        return $session;
    }

    private function studentFor(User $user): Student
    {
        $student = Student::where('user_id', $user->id)->first();
        if (! $student) {
            throw new ClassSessionException(__('Data siswa untuk akun ini tidak ditemukan.'), 404, errorCode: 'student_not_found');
        }

        return $student;
    }

    /**
     * Relations and attendance counters the session list/detail responses need.
     */
    public function withListData($query)
    {
        return $query
            ->with(['subject', 'classroom', 'teacher'])
            ->withCount([
                'attendances as total_students',
                'attendances as present_count' => fn ($q) => $q->whereIn('status', SessionAttendance::PRESENT_STATUSES),
                'attendances as absent_count' => fn ($q) => $q->where('status', SessionAttendance::STATUS_ALPHA),
            ]);
    }

    public function loadDetail(ClassSession $session): ClassSession
    {
        return $this->withListData(ClassSession::whereKey($session->id))->firstOrFail();
    }

    private function audit(?User $actor, string $event, $model, ?array $old, ?array $new, ?string $ip = null): void
    {
        try {
            ActivityLog::create([
                'user_id' => $actor?->id,
                'event' => $event,
                'auditable_type' => get_class($model),
                'auditable_id' => $model->id,
                'old_values' => $old,
                'new_values' => $new,
                'ip_address' => $ip,
            ]);
        } catch (Throwable $e) {
            Log::warning('ClassSession: audit log failed', ['event' => $event, 'error' => $e->getMessage()]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Reminders & parent digest (77.7.1)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Push the teacher "starts in N minutes" for lessons about to begin and
     * "ends in N minutes" for active lessons about to finish. Each reminder
     * is claimed with a conditional UPDATE, so it goes out once even if the
     * scheduler overlaps or runs on several servers.
     *
     * @return array{start: int, end: int}
     */
    public function sendDueReminders(?Carbon $now = null): array
    {
        $now ??= now();
        $lead = max(1, (int) config('class_sessions.reminder_lead_minutes', 5));
        $date = $now->toDateString();
        $sent = ['start' => 0, 'end' => 0];

        // Starting soon: only materialise the timetable entries that are due.
        $schedules = Schedule::where('day', self::dayName($now))
            ->when(AcademicYear::current(), fn ($q, $year) => $q->where('academic_year_id', $year->id))
            ->get();
        foreach ($schedules as $schedule) {
            $times = $this->resolveTimes($schedule);
            if (! $times || ! $this->isDueWithin(Carbon::parse("{$date} {$times['start']}"), $now, $lead)) {
                continue;
            }
            $session = $this->materialize($schedule, $date);
            if ($session && $this->claimReminder($session, 'start_reminder_sent_at', ClassSession::STATUS_SCHEDULED, $now)) {
                $session->loadMissing(['subject', 'classroom']);
                $this->push([$session->teacher_id],
                    "Sesi {$session->subject?->name} {$session->classroom?->name} dimulai {$lead} menit lagi",
                    'Tap untuk memulai kelas.',
                    ['type' => 'class_session_start_reminder', 'session_id' => (string) $session->id, 'schedule_id' => (string) $schedule->id]
                );
                $sent['start']++;
            }
        }

        // Ending soon: active lessons today.
        $active = $this->withListData(ClassSession::active()->forDate($date))->get();
        foreach ($active as $session) {
            if (! $this->isDueWithin($session->scheduledEndAt(), $now, $lead)
                || ! $this->claimReminder($session, 'end_reminder_sent_at', ClassSession::STATUS_ACTIVE, $now)) {
                continue;
            }
            $this->push([$session->teacher_id],
                "Sesi {$session->subject?->name} {$session->classroom?->name} berakhir {$lead} menit lagi",
                $session->absent_count > 0 ? "{$session->absent_count} siswa belum absen." : 'Semua siswa sudah tercatat.',
                ['type' => 'class_session_end_reminder', 'session_id' => (string) $session->id]
            );
            $sent['end']++;
        }

        return $sent;
    }

    /**
     * One push per child to the parent (students.parent_id) summarising the
     * lessons actually held that day, e.g. "6/7 sesi hadir. Alpha:
     * Matematika (Jam ke-3)." Children with no held lesson get nothing.
     *
     * @return int pushes dispatched
     */
    public function sendParentDigest(string $date): int
    {
        $rows = SessionAttendance::query()
            ->join('class_sessions as cs', 'cs.id', '=', 'session_attendances.class_session_id')
            ->join('students as st', 'st.id', '=', 'session_attendances.student_id')
            ->leftJoin('subjects as sub', 'sub.id', '=', 'cs.subject_id')
            ->whereNull('cs.deleted_at')
            ->whereIn('cs.status', ClassSession::HELD_STATUSES)
            ->where('cs.session_date', $date)
            ->whereNotNull('st.parent_id')
            ->orderBy('cs.scheduled_start')
            ->get([
                'session_attendances.student_id',
                'session_attendances.status',
                'st.parent_id',
                'cs.jam_ke',
                'sub.name as subject_name',
            ])
            ->groupBy('student_id');

        if ($rows->isEmpty()) {
            return 0;
        }

        $names = Student::with('user:id,name')->whereIn('id', $rows->keys())->get()
            ->mapWithKeys(fn (Student $st) => [$st->id => $st->user?->name ?? 'Ananda']);

        $sent = 0;
        foreach ($rows as $studentId => $lessons) {
            $present = $lessons->whereIn('status', SessionAttendance::PRESENT_STATUSES)->count();
            $late = $lessons->where('status', SessionAttendance::STATUS_TELAT)->count();
            $excused = $lessons->whereIn('status', [SessionAttendance::STATUS_SAKIT, SessionAttendance::STATUS_IZIN])->count();
            $alpha = $lessons->where('status', SessionAttendance::STATUS_ALPHA)
                ->map(fn ($l) => ($l->subject_name ?? 'Mapel')." (Jam ke-{$l->jam_ke})")
                ->implode(', ');

            $body = "{$present}/{$lessons->count()} sesi hadir.";
            if ($alpha !== '') {
                $body .= " Alpha: {$alpha}.";
            }
            if ($late > 0) {
                $body .= " Terlambat: {$late}.";
            }
            if ($excused > 0) {
                $body .= " Sakit/izin: {$excused}.";
            }

            $this->push([(int) $lessons->first()->parent_id],
                "Kehadiran {$names[$studentId]} hari ini",
                $body,
                ['type' => 'class_session_daily_digest', 'student_id' => (string) $studentId, 'date' => $date]
            );
            $sent++;
        }

        return $sent;
    }

    /** Is $at in the next $minutes (after now, at most $minutes away)? */
    private function isDueWithin(Carbon $at, Carbon $now, int $minutes): bool
    {
        return $at->gt($now) && $at->lte($now->copy()->addMinutes($minutes));
    }

    private function claimReminder(ClassSession $session, string $column, string $status, Carbon $now): bool
    {
        return ClassSession::whereKey($session->id)
            ->where('status', $status)
            ->whereNull($column)
            ->update([$column => $now]) === 1;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Push notifications (77.7.1)
    // ─────────────────────────────────────────────────────────────────────

    private function notifyStudentsSessionStarted(ClassSession $session): void
    {
        $session->loadMissing(['subject', 'teacher']);
        $userIds = Student::whereIn('id', $session->attendances()->pluck('student_id'))
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->all();

        $this->push($userIds,
            "Sesi {$session->subject?->name} dimulai!",
            "Segera scan QR dari layar {$session->teacher?->name}.",
            ['type' => 'class_session_started', 'session_id' => (string) $session->id]
        );
    }

    private function notifySessionClosed(ClassSession $session, bool $auto): void
    {
        $subject = $session->subject?->name;
        $alphaUserIds = Student::whereIn('id', $session->attendances()
            ->where('status', SessionAttendance::STATUS_ALPHA)
            ->pluck('student_id'))
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->all();

        $this->push($alphaUserIds,
            "Anda tercatat ALPHA di sesi {$subject}",
            'Hubungi Waka Kurikulum/TU jika perlu koreksi presensi.',
            ['type' => 'class_session_alpha', 'session_id' => (string) $session->id]
        );

        if ($auto) {
            $this->push([$session->teacher_id],
                "Sesi {$subject} {$session->classroom?->name} ditutup otomatis",
                "{$session->absent_count} siswa tercatat alpha.",
                ['type' => 'class_session_auto_closed', 'session_id' => (string) $session->id]
            );
        }
    }

    private function push(array $userIds, string $title, string $body, array $payload): void
    {
        if ($userIds === []) {
            return;
        }

        try {
            foreach (array_chunk(array_values(array_unique($userIds)), 500) as $chunk) {
                $this->fcm->sendToUsers($chunk, $title, $body, $payload, 'attendance_alerts');
            }
        } catch (Throwable $e) {
            Log::warning('ClassSession: push notification failed', ['title' => $title, 'error' => $e->getMessage()]);
        }
    }

    /** "hadir" → "Hadir" / "Present" / "حاضر": a status code in the user's language. */
    private function statusLabel(string $status): string
    {
        return __(ucfirst($status));
    }
}
