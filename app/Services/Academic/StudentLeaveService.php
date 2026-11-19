<?php

namespace App\Services\Academic;

use App\Models\AcademicYear;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\ClassSession;
use App\Models\PositionAssignment;
use App\Models\SessionAttendance;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentLeaveRequest;
use App\Models\User;
use App\Notifications\StudentLeaveStatusNotification;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;

class StudentLeaveService
{
    public const DEFAULT_AUTO_APPROVE_SICK_DAYS = 1;

    public const DEFAULT_MEDICAL_LETTER_AFTER_DAYS = 2;

    public const DEFAULT_ESCALATION_HOURS = 24;

    public function autoApproveSickDays(): int
    {
        $configured = Setting::get('student_leave_auto_approve_sick_days', self::DEFAULT_AUTO_APPROVE_SICK_DAYS);

        return max(0, (int) ($configured ?? self::DEFAULT_AUTO_APPROVE_SICK_DAYS));
    }

    public function medicalLetterAfterDays(): int
    {
        $configured = Setting::get('student_leave_medical_letter_after_days', self::DEFAULT_MEDICAL_LETTER_AFTER_DAYS);

        return max(1, (int) ($configured ?? self::DEFAULT_MEDICAL_LETTER_AFTER_DAYS));
    }

    public function escalationHours(): int
    {
        $configured = Setting::get('student_leave_escalation_hours', self::DEFAULT_ESCALATION_HOURS);

        return max(1, (int) ($configured ?? self::DEFAULT_ESCALATION_HOURS));
    }

    /**
     * Parent submits a leave/sick request for their child.
     * Auto-approves 1-day sick leaves when configured; requires medical letter beyond N days.
     */
    public function submit(
        User $parent,
        Student $student,
        array $data,
        ?UploadedFile $attachment = null,
    ): StudentLeaveRequest {
        if ((int) $student->parent_id !== (int) $parent->id) {
            throw new AuthorizationException(__('Anda hanya dapat mengajukan izin untuk anak Anda sendiri.'));
        }

        $type = (string) ($data['type'] ?? '');
        if (! in_array($type, [StudentLeaveRequest::TYPE_SAKIT, StudentLeaveRequest::TYPE_IZIN], true)) {
            throw ValidationException::withMessages([
                'type' => __('Jenis pengajuan harus berupa sakit atau izin.'),
            ]);
        }

        $start = CarbonImmutable::parse((string) $data['start_date'])->startOfDay();
        $end = CarbonImmutable::parse((string) ($data['end_date'] ?? $data['start_date']))->startOfDay();

        if ($end->lt($start)) {
            throw ValidationException::withMessages([
                'end_date' => __('Tanggal selesai tidak boleh lebih awal dari tanggal mulai.'),
            ]);
        }

        $daysCount = (int) $start->diffInDays($end) + 1;
        $medicalLimit = $this->medicalLetterAfterDays();
        if ($type === StudentLeaveRequest::TYPE_SAKIT && $daysCount > $medicalLimit && $attachment === null && empty($data['attachment_path'])) {
            throw ValidationException::withMessages([
                'attachment' => __('Surat keterangan dokter wajib dilampirkan untuk izin sakit lebih dari :days hari.', [
                    'days' => $medicalLimit,
                ]),
            ]);
        }

        $hasOverlap = StudentLeaveRequest::query()
            ->where('student_id', $student->id)
            ->whereIn('status', StudentLeaveRequest::ACTIVE_STATUSES)
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString())
            ->exists();

        if ($hasOverlap) {
            throw ValidationException::withMessages([
                'start_date' => __('Sudah terdapat pengajuan izin/sakit aktif pada rentang tanggal tersebut.'),
            ]);
        }

        $attachmentPath = $attachment?->store("student-leaves/{$student->id}", 'local');
        if ($attachmentPath === false) {
            $attachmentPath = null;
        }

        $autoApproveDays = $this->autoApproveSickDays();
        $autoApprove = $type === StudentLeaveRequest::TYPE_SAKIT
            && $autoApproveDays > 0
            && $daysCount <= $autoApproveDays;

        $academicYearId = AcademicYear::current()?->id;

        $leave = DB::transaction(function () use (
            $parent,
            $student,
            $type,
            $start,
            $end,
            $daysCount,
            $data,
            $attachmentPath,
            $autoApprove,
            $autoApproveDays,
            $academicYearId
        ) {
            $leave = StudentLeaveRequest::create([
                'student_id' => $student->id,
                'classroom_id' => $student->classroom_id,
                'academic_year_id' => $academicYearId,
                'submitted_by' => $parent->id,
                'type' => $type,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'days_count' => $daysCount,
                'reason' => trim((string) ($data['reason'] ?? '')),
                'attachment_path' => $attachmentPath,
                'status' => $autoApprove ? StudentLeaveRequest::STATUS_AUTO_APPROVED : StudentLeaveRequest::STATUS_PENDING,
                'auto_approved' => $autoApprove,
                'decided_by' => null,
                'decided_at' => $autoApprove ? now() : null,
                'decision_notes' => $autoApprove
                    ? __('Disetujui otomatis oleh sistem (izin sakit ≤ :days hari).', ['days' => $autoApproveDays])
                    : null,
            ]);

            if ($autoApprove) {
                $this->syncLeaveToAttendancesUpToToday($leave);
            }

            ActivityLog::create([
                'user_id' => $parent->id,
                'event' => $autoApprove ? 'student_leave.auto_approved' : 'student_leave.submitted',
                'auditable_type' => StudentLeaveRequest::class,
                'auditable_id' => $leave->id,
                'new_values' => [
                    'student_id' => $student->id,
                    'type' => $type,
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'status' => $leave->status,
                ],
            ]);

            return $leave;
        });

        $leave->load(['student.user', 'classroom.teacher']);

        if ($leave->classroom?->teacher && (int) $leave->classroom->teacher->id !== (int) $parent->id) {
            $this->safeNotify(
                $leave->classroom->teacher,
                new StudentLeaveStatusNotification($leave, $autoApprove ? 'auto_approved' : 'submitted')
            );
        }

        if ($autoApprove) {
            $this->safeNotify($parent, new StudentLeaveStatusNotification($leave, 'auto_approved'));
        }

        return $leave->fresh(['student.user', 'classroom.teacher', 'submitter', 'decider']);
    }

    /**
     * Strict permission & anti-self-approval check for approving/rejecting/revoking a leave.
     */
    public function canManageLeave(User $actor, StudentLeaveRequest $leave): bool
    {
        if (($actor->account_status ?? 'aktif') === 'nonaktif') {
            return false;
        }

        $leave->loadMissing(['student.classroom', 'classroom']);

        if ((int) $actor->id === (int) $leave->submitted_by || (int) $actor->id === (int) $leave->student?->parent_id) {
            return false;
        }

        $roles = $actor->effectiveRoles();
        if (array_intersect($roles, ['superadmin', 'admin', 'waka_kesiswaan']) !== []) {
            return true;
        }

        $homeroomTeacherId = $leave->classroom?->teacher_id ?? $leave->student?->classroom?->teacher_id;

        return $homeroomTeacherId !== null && (int) $homeroomTeacherId === (int) $actor->id;
    }

    public function canViewAttachment(User $actor, StudentLeaveRequest $leave): bool
    {
        if (($actor->account_status ?? 'aktif') === 'nonaktif') {
            return false;
        }

        $leave->loadMissing(['student.classroom', 'classroom']);

        if ((int) $actor->id === (int) $leave->submitted_by
            || (int) $actor->id === (int) $leave->student?->parent_id
            || (int) $actor->id === (int) $leave->student?->user_id) {
            return true;
        }

        $roles = $actor->effectiveRoles();
        if (array_intersect($roles, ['superadmin', 'admin', 'waka_kesiswaan', 'kepala_sekolah', 'kepsek', 'staf_tu', 'bk', 'petugas_uks']) !== []) {
            return true;
        }

        $homeroomTeacherId = $leave->classroom?->teacher_id ?? $leave->student?->classroom?->teacher_id;

        return $homeroomTeacherId !== null && (int) $homeroomTeacherId === (int) $actor->id;
    }

    public function approve(User $actor, StudentLeaveRequest $leave, ?string $notes = null): StudentLeaveRequest
    {
        $this->assertCanDecide($actor, $leave);

        $updated = DB::transaction(function () use ($actor, $leave, $notes) {
            /** @var StudentLeaveRequest $locked */
            $locked = StudentLeaveRequest::whereKey($leave->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== StudentLeaveRequest::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'status' => __('Hanya pengajuan berstatus menunggu yang dapat disetujui.'),
                ]);
            }

            $locked->update([
                'status' => StudentLeaveRequest::STATUS_APPROVED,
                'auto_approved' => false,
                'decided_by' => $actor->id,
                'decided_at' => now(),
                'decision_notes' => $notes !== null && trim($notes) !== '' ? trim($notes) : null,
            ]);

            $this->syncLeaveToAttendancesUpToToday($locked);

            ActivityLog::create([
                'user_id' => $actor->id,
                'event' => 'student_leave.approved',
                'auditable_type' => StudentLeaveRequest::class,
                'auditable_id' => $locked->id,
                'old_values' => ['status' => StudentLeaveRequest::STATUS_PENDING],
                'new_values' => ['status' => StudentLeaveRequest::STATUS_APPROVED],
            ]);

            return $locked;
        });

        $updated->loadMissing('submitter');
        if ($updated->submitter) {
            $this->safeNotify($updated->submitter, new StudentLeaveStatusNotification($updated, 'approved'));
        }

        return $updated->fresh(['student.user', 'classroom.teacher', 'submitter', 'decider']);
    }

    public function reject(User $actor, StudentLeaveRequest $leave, string $reason): StudentLeaveRequest
    {
        $this->assertCanDecide($actor, $leave);

        $cleanReason = trim($reason);
        if ($cleanReason === '') {
            throw ValidationException::withMessages([
                'decision_notes' => __('Alasan penolakan wajib diisi.'),
            ]);
        }

        $updated = DB::transaction(function () use ($actor, $leave, $cleanReason) {
            /** @var StudentLeaveRequest $locked */
            $locked = StudentLeaveRequest::whereKey($leave->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== StudentLeaveRequest::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'status' => __('Hanya pengajuan berstatus menunggu yang dapat ditolak.'),
                ]);
            }

            $locked->update([
                'status' => StudentLeaveRequest::STATUS_REJECTED,
                'decided_by' => $actor->id,
                'decided_at' => now(),
                'decision_notes' => $cleanReason,
            ]);

            ActivityLog::create([
                'user_id' => $actor->id,
                'event' => 'student_leave.rejected',
                'auditable_type' => StudentLeaveRequest::class,
                'auditable_id' => $locked->id,
                'old_values' => ['status' => StudentLeaveRequest::STATUS_PENDING],
                'new_values' => ['status' => StudentLeaveRequest::STATUS_REJECTED, 'decision_notes' => $cleanReason],
            ]);

            return $locked;
        });

        $updated->loadMissing('submitter');
        if ($updated->submitter) {
            $this->safeNotify($updated->submitter, new StudentLeaveStatusNotification($updated, 'rejected'));
        }

        return $updated->fresh(['student.user', 'classroom.teacher', 'submitter', 'decider']);
    }

    /**
     * Revoke a previously approved / auto-approved leave (e.g., invalid doctor note)
     * and surgically revert attendance rows generated by this leave request.
     */
    public function revoke(User $actor, StudentLeaveRequest $leave, string $reason): StudentLeaveRequest
    {
        $this->assertCanDecide($actor, $leave);

        $cleanReason = trim($reason);
        if ($cleanReason === '') {
            throw ValidationException::withMessages([
                'decision_notes' => __('Alasan pencabutan izin wajib diisi.'),
            ]);
        }

        $updated = DB::transaction(function () use ($actor, $leave, $cleanReason) {
            /** @var StudentLeaveRequest $locked */
            $locked = StudentLeaveRequest::whereKey($leave->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isApproved()) {
                throw ValidationException::withMessages([
                    'status' => __('Hanya izin yang sudah disetujui yang dapat dicabut.'),
                ]);
            }

            $oldStatus = $locked->status;
            $locked->update([
                'status' => StudentLeaveRequest::STATUS_REJECTED,
                'decided_by' => $actor->id,
                'decided_at' => now(),
                'decision_notes' => $cleanReason,
            ]);

            $this->revertSyncedAttendances($locked);

            ActivityLog::create([
                'user_id' => $actor->id,
                'event' => 'student_leave.revoked',
                'auditable_type' => StudentLeaveRequest::class,
                'auditable_id' => $locked->id,
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => StudentLeaveRequest::STATUS_REJECTED, 'decision_notes' => $cleanReason],
            ]);

            return $locked;
        });

        $updated->loadMissing('submitter');
        if ($updated->submitter) {
            $this->safeNotify($updated->submitter, new StudentLeaveStatusNotification($updated, 'revoked'));
        }

        return $updated->fresh(['student.user', 'classroom.teacher', 'submitter', 'decider']);
    }

    public function cancelByParent(User $parent, StudentLeaveRequest $leave): StudentLeaveRequest
    {
        $leave->loadMissing('student');
        if ((int) $leave->submitted_by !== (int) $parent->id && (int) $leave->student?->parent_id !== (int) $parent->id) {
            throw new AuthorizationException(__('Anda hanya dapat membatalkan pengajuan milik Anda sendiri.'));
        }

        $updated = DB::transaction(function () use ($parent, $leave) {
            /** @var StudentLeaveRequest $locked */
            $locked = StudentLeaveRequest::whereKey($leave->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== StudentLeaveRequest::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'status' => __('Pengajuan yang sudah diputuskan tidak dapat dibatalkan oleh orang tua. Hubungi wali kelas bila perlu perubahan.'),
                ]);
            }

            $locked->update([
                'status' => StudentLeaveRequest::STATUS_CANCELLED,
                'decided_by' => $parent->id,
                'decided_at' => now(),
            ]);

            ActivityLog::create([
                'user_id' => $parent->id,
                'event' => 'student_leave.cancelled',
                'auditable_type' => StudentLeaveRequest::class,
                'auditable_id' => $locked->id,
                'old_values' => ['status' => StudentLeaveRequest::STATUS_PENDING],
                'new_values' => ['status' => StudentLeaveRequest::STATUS_CANCELLED],
            ]);

            return $locked;
        });

        $updated->loadMissing('classroom.teacher');
        if ($updated->classroom?->teacher && (int) $updated->classroom->teacher->id !== (int) $parent->id) {
            $this->safeNotify($updated->classroom->teacher, new StudentLeaveStatusNotification($updated, 'cancelled'));
        }

        return $updated->fresh(['student.user', 'classroom.teacher', 'submitter']);
    }

    /**
     * Sync an approved leave into daily `attendances` (never future-dated!)
     * and any already-opened `session_attendances` in the date range.
     */
    public function syncLeaveToAttendancesUpToToday(StudentLeaveRequest $leave, ?Carbon $now = null): int
    {
        if (! $leave->isApproved()) {
            return 0;
        }

        $leave->loadMissing('student');
        $now ??= now();
        $today = CarbonImmutable::instance($now)->startOfDay();
        $start = CarbonImmutable::parse($leave->start_date)->startOfDay();
        $end = CarbonImmutable::parse($leave->end_date)->startOfDay();
        $effectiveEnd = $end->lt($today) ? $end : $today;

        if ($start->gt($effectiveEnd)) {
            return 0;
        }

        $classroomId = $leave->classroom_id ?? $leave->student?->classroom_id;
        $academicYearId = $leave->academic_year_id
            ?? AcademicYear::current()?->id
            ?? AcademicYear::query()->latest('id')->value('id');
        $recorderId = $leave->decided_by ?? $leave->submitted_by;

        $dailyCode = $leave->dailyAttendanceCode();
        $sessionStatus = $leave->sessionAttendanceStatus();
        $leaveNote = __('Izin disetujui (:type): :reason', [
            'type' => strtoupper($leave->type),
            'reason' => $leave->reason,
        ]);

        $synced = 0;

        if ($classroomId && $academicYearId && $recorderId) {
            for ($cursor = $start; $cursor->lte($effectiveEnd); $cursor = $cursor->addDay()) {
                $dateStr = $cursor->toDateString();
                $existing = Attendance::query()
                    ->where('student_id', $leave->student_id)
                    ->where('date', $dateStr)
                    ->whereNull('schedule_id')
                    ->first();

                if ($existing) {
                    if ($existing->status !== 'H' || (int) $existing->student_leave_request_id === (int) $leave->id) {
                        $existing->update([
                            'academic_year_id' => $academicYearId,
                            'classroom_id' => $classroomId,
                            'status' => $dailyCode,
                            'notes' => $leaveNote,
                            'recorded_by' => $recorderId,
                            'student_leave_request_id' => $leave->id,
                        ]);
                        $synced++;
                    }
                } else {
                    Attendance::create([
                        'student_id' => $leave->student_id,
                        'academic_year_id' => $academicYearId,
                        'classroom_id' => $classroomId,
                        'schedule_id' => null,
                        'date' => $dateStr,
                        'status' => $dailyCode,
                        'notes' => $leaveNote,
                        'recorded_by' => $recorderId,
                        'student_leave_request_id' => $leave->id,
                    ]);
                    $synced++;
                }
            }
        }

        $sessions = ClassSession::query()
            ->whereBetween('session_date', [$start->toDateString(), $effectiveEnd->toDateString()])
            ->where('status', '!=', ClassSession::STATUS_CANCELLED)
            ->where(function ($q) use ($classroomId, $leave) {
                if ($classroomId) {
                    $q->where('classroom_id', $classroomId);
                }
                $q->orWhereHas('attendances', fn ($aq) => $aq->where('student_id', $leave->student_id));
            })
            ->get();

        foreach ($sessions as $session) {
            $sessionAttendance = SessionAttendance::query()
                ->where('class_session_id', $session->id)
                ->where('student_id', $leave->student_id)
                ->first();

            if ($sessionAttendance) {
                if (! in_array($sessionAttendance->status, SessionAttendance::PRESENT_STATUSES, true)) {
                    $sessionAttendance->update([
                        'status' => $sessionStatus,
                        'check_in_method' => SessionAttendance::METHOD_MANUAL_TEACHER,
                        'checked_in_at' => null,
                        'recorded_by' => $recorderId,
                        'student_leave_request_id' => $leave->id,
                        'notes' => $leaveNote,
                    ]);
                    $synced++;
                }
            } elseif ($session->status !== ClassSession::STATUS_SCHEDULED) {
                SessionAttendance::create([
                    'class_session_id' => $session->id,
                    'student_id' => $leave->student_id,
                    'status' => $sessionStatus,
                    'check_in_method' => SessionAttendance::METHOD_MANUAL_TEACHER,
                    'checked_in_at' => null,
                    'recorded_by' => $recorderId,
                    'student_leave_request_id' => $leave->id,
                    'notes' => $leaveNote,
                ]);
                $synced++;
            }
        }

        return $synced;
    }

    public function revertSyncedAttendances(StudentLeaveRequest $leave): void
    {
        Attendance::query()
            ->where('student_leave_request_id', $leave->id)
            ->delete();

        $revokeNote = __('Izin dicabut: :reason', [
            'reason' => $leave->decision_notes ?? '-',
        ]);

        SessionAttendance::query()
            ->where('student_leave_request_id', $leave->id)
            ->whereNotIn('status', SessionAttendance::PRESENT_STATUSES)
            ->update([
                'status' => SessionAttendance::STATUS_ALPHA,
                'check_in_method' => SessionAttendance::METHOD_AUTO_ALPHA,
                'checked_in_at' => null,
                'student_leave_request_id' => null,
                'notes' => $revokeNote,
            ]);
    }

    /**
     * Scheduler task:
     * 1. Materialize today's attendance rows for approved leaves covering today.
     * 2. Escalate pending leaves older than configured threshold to Waka Kesiswaan.
     *
     * @return array{synced: int, escalated: int}
     */
    public function processDailyAndEscalate(?Carbon $now = null): array
    {
        $now ??= now();
        $today = $now->toDateString();

        $approvedForToday = StudentLeaveRequest::query()
            ->whereIn('status', StudentLeaveRequest::APPROVED_STATUSES)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->get();

        $syncedCount = 0;
        foreach ($approvedForToday as $leave) {
            $syncedCount += $this->syncLeaveToAttendancesUpToToday($leave, $now);
        }

        $cutoff = $now->copy()->subHours($this->escalationHours());
        $overduePending = StudentLeaveRequest::query()
            ->where('status', StudentLeaveRequest::STATUS_PENDING)
            ->whereNull('escalated_at')
            ->where('created_at', '<=', $cutoff)
            ->get();

        $escalatedCount = 0;
        if ($overduePending->isNotEmpty()) {
            $recipients = $this->resolveActiveRoleHolders(['waka_kesiswaan']);
            if ($recipients->isEmpty()) {
                $recipients = $this->resolveActiveRoleHolders(['admin']);
            }
            $primaryRecipient = $recipients->first();

            foreach ($overduePending as $pendingLeave) {
                $pendingLeave->update([
                    'escalated_at' => $now,
                    'escalated_to' => $primaryRecipient?->id,
                ]);

                foreach ($recipients as $recipient) {
                    $this->safeNotify($recipient, new StudentLeaveStatusNotification($pendingLeave, 'escalated'));
                }

                $escalatedCount++;
            }
        }

        return [
            'synced' => $syncedCount,
            'escalated' => $escalatedCount,
        ];
    }

    /**
     * List classrooms accessible by homeroom teacher or Kesiswaan/Admin operator.
     *
     * @return Collection<int, int>|null
     */
    public function accessibleClassroomIds(User $actor): ?Collection
    {
        $roles = $actor->effectiveRoles();
        if (array_intersect($roles, ['superadmin', 'admin', 'waka_kesiswaan']) !== []) {
            return null;
        }

        $year = AcademicYear::current();

        return Classroom::query()
            ->where('teacher_id', $actor->id)
            ->when($year, fn ($q) => $q->where('academic_year', $year->year))
            ->pluck('id');
    }

    /**
     * Find active users holding any of the given role/position codes (Layer 2 aware).
     *
     * @param  list<string>  $roles
     * @return Collection<int, User>
     */
    public function resolveActiveRoleHolders(array $roles): Collection
    {
        $byBaseRole = User::query()
            ->whereIn('role', $roles)
            ->where(fn ($q) => $q->whereNull('account_status')->orWhere('account_status', '!=', 'nonaktif'))
            ->get();

        $byAssignment = collect();
        if (Schema::hasTable('position_assignments') && Schema::hasTable('positions')) {
            $userIds = PositionAssignment::query()
                ->activeOn()
                ->whereHas('position', fn ($q) => $q->whereIn('code', $roles)->where('is_active', true))
                ->pluck('user_id');

            if ($userIds->isNotEmpty()) {
                $byAssignment = User::query()
                    ->whereIn('id', $userIds)
                    ->where(fn ($q) => $q->whereNull('account_status')->orWhere('account_status', '!=', 'nonaktif'))
                    ->get();
            }
        }

        return $byBaseRole->merge($byAssignment)->unique('id')->values();
    }

    private function assertCanDecide(User $actor, StudentLeaveRequest $leave): void
    {
        $leave->loadMissing('student');
        if ((int) $actor->id === (int) $leave->submitted_by || (int) $actor->id === (int) $leave->student?->parent_id) {
            throw new AuthorizationException(__('Pengaju izin tidak dapat memproses atau menyetujui pengajuannya sendiri.'));
        }

        if (! $this->canManageLeave($actor, $leave)) {
            throw new AuthorizationException(__('Anda tidak berwenang memproses pengajuan izin siswa ini.'));
        }
    }

    private function safeNotify(User $recipient, StudentLeaveStatusNotification $notification): void
    {
        try {
            $recipient->notify($notification);
        } catch (Throwable $e) {
            Log::warning('StudentLeaveService: notification failed', [
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
