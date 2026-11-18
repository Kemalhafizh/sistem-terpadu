<?php

namespace App\Services\Academic;

use App\Models\ClassEnrollment;
use App\Models\ClassPlacement;
use App\Models\Classroom;
use App\Models\PromotionBatch;
use App\Models\PromotionDecision;
use App\Models\SchoolYear;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollmentService
{
    /**
     * Enroll a student into a classroom for a school year, ensuring at most one
     * `aktif` enrollment per student and syncing `students.classroom_id`.
     */
    public function enroll(
        Student $student,
        Classroom $classroom,
        SchoolYear $schoolYear,
        ?CarbonInterface $startedOn = null,
        ?User $decidedBy = null,
        ?int $promotionDecisionId = null,
    ): ClassEnrollment {
        return DB::transaction(function () use ($student, $classroom, $schoolYear, $startedOn, $decidedBy, $promotionDecisionId) {
            $startDate = ($startedOn ? CarbonImmutable::instance($startedOn) : CarbonImmutable::today())->toDateString();

            $existingActive = ClassEnrollment::query()
                ->where('student_id', $student->id)
                ->where('status', ClassEnrollment::STATUS_ACTIVE)
                ->lockForUpdate()
                ->get();

            foreach ($existingActive as $active) {
                if ((int) $active->classroom_id === (int) $classroom->id && (int) $active->school_year_id === (int) $schoolYear->id) {
                    if ((int) $student->classroom_id !== (int) $classroom->id) {
                        $student->update(['classroom_id' => $classroom->id]);
                    }

                    return $active;
                }

                $closeStatus = (int) $active->school_year_id === (int) $schoolYear->id
                    ? ClassEnrollment::STATUS_TRANSFERRED_CLASS
                    : ClassEnrollment::STATUS_PROMOTED;

                $active->update([
                    'status' => $closeStatus,
                    'ended_on' => $startDate,
                    'ended_reason' => $closeStatus === ClassEnrollment::STATUS_TRANSFERRED_CLASS ? 'Pindah rombel' : 'Pergantian tahun ajaran',
                    'decided_by' => $decidedBy?->id,
                ]);
            }

            $enrollment = ClassEnrollment::create([
                'student_id' => $student->id,
                'classroom_id' => $classroom->id,
                'school_year_id' => $schoolYear->id,
                'status' => ClassEnrollment::STATUS_ACTIVE,
                'started_on' => $startDate,
                'decided_by' => $decidedBy?->id,
                'promotion_decision_id' => $promotionDecisionId,
            ]);

            $student->update(['classroom_id' => $classroom->id]);

            return $enrollment;
        });
    }

    /**
     * Close a student's active enrollment (e.g. graduation or outward mutation).
     */
    public function closeActiveEnrollment(
        Student $student,
        string $status,
        ?CarbonInterface $endedOn = null,
        ?string $reason = null,
        ?User $decidedBy = null,
    ): ?ClassEnrollment {
        return DB::transaction(function () use ($student, $status, $endedOn, $reason, $decidedBy) {
            $endDate = ($endedOn ? CarbonImmutable::instance($endedOn) : CarbonImmutable::today())->toDateString();

            $active = ClassEnrollment::query()
                ->where('student_id', $student->id)
                ->where('status', ClassEnrollment::STATUS_ACTIVE)
                ->lockForUpdate()
                ->first();

            if ($active !== null) {
                $active->update([
                    'status' => $status,
                    'ended_on' => $endDate,
                    'ended_reason' => $reason,
                    'decided_by' => $decidedBy?->id,
                ]);
            }

            if (in_array($status, [ClassEnrollment::STATUS_GRADUATED, ClassEnrollment::STATUS_MUTATED_OUT, ClassEnrollment::STATUS_LEFT], true)) {
                $studentStatus = $status === ClassEnrollment::STATUS_GRADUATED ? 'graduated' : 'inactive';
                $student->update(['status' => $studentStatus]);

                if ($student->user !== null) {
                    $student->user->update([
                        'account_status' => 'nonaktif',
                        'deactivated_reason' => $status,
                    ]);
                }
            }

            return $active;
        });
    }

    /**
     * Draft a promotion batch from an active school year to the next school year.
     * When school promotion thresholds are not configured, `decision` is left `null`
     * (never fabricates automatic labels).
     */
    public function createPromotionBatch(SchoolYear $fromYear, SchoolYear $toYear, User $creator): PromotionBatch
    {
        return DB::transaction(function () use ($fromYear, $toYear, $creator) {
            $batch = PromotionBatch::query()->firstOrCreate(
                [
                    'from_school_year_id' => $fromYear->id,
                    'to_school_year_id' => $toYear->id,
                ],
                [
                    'status' => PromotionBatch::STATUS_DRAFT,
                ]
            );

            $activeEnrollments = ClassEnrollment::query()
                ->where('school_year_id', $fromYear->id)
                ->where('status', ClassEnrollment::STATUS_ACTIVE)
                ->with(['student', 'classroom'])
                ->get();

            $maxBelowKktp = Setting::get('promotion_max_below_kktp');
            $minAttendance = Setting::get('promotion_min_attendance_percent');
            $hasThresholds = is_numeric($maxBelowKktp) && is_numeric($minAttendance);

            foreach ($activeEnrollments as $enrollment) {
                $student = $enrollment->student;
                $fromClassroom = $enrollment->classroom;
                if (! $student || ! $fromClassroom) {
                    continue;
                }

                $placement = ClassPlacement::query()
                    ->where('school_year_id', $toYear->id)
                    ->where('student_id', $student->id)
                    ->first();

                $basis = [
                    'below_kktp_count' => null,
                    'attendance_percent' => null,
                    'thresholds_configured' => $hasThresholds,
                ];

                $autoDecision = null;
                if ($hasThresholds) {
                    $isGrade12 = in_array((string) $fromClassroom->grade_level, ['XII', '12'], true);
                    $autoDecision = $isGrade12 ? 'lulus' : 'naik';
                }

                PromotionDecision::query()->firstOrCreate(
                    [
                        'promotion_batch_id' => $batch->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'from_classroom_id' => $fromClassroom->id,
                        'decision' => $autoDecision,
                        'to_classroom_id' => $placement?->classroom_id,
                        'basis' => $basis,
                        'decided_by' => $autoDecision !== null ? $creator->id : null,
                    ]
                );
            }

            return $batch->load('decisions');
        });
    }

    /**
     * Homeroom teacher reviews and records a student's promotion decision and note.
     */
    public function reviewByHomeroom(
        PromotionDecision $decision,
        User $reviewer,
        string $decisionValue,
        ?string $note = null,
        ?int $toClassroomId = null,
    ): PromotionDecision {
        $fromClassroom = $decision->fromClassroom;
        $isHomeroom = $fromClassroom !== null && (int) $fromClassroom->teacher_id === (int) $reviewer->id;

        if (! $isHomeroom && ! $reviewer->hasPermission('kurikulum.tetapkan_kenaikan')) {
            abort(403, __('Hanya wali kelas rombel terkait atau Waka Kurikulum yang dapat meninjau keputusan kenaikan kelas ini.'));
        }

        if (! in_array($decisionValue, ['naik', 'tinggal', 'lulus'], true)) {
            throw ValidationException::withMessages([
                'decision' => __('Keputusan harus berupa naik, tinggal, atau lulus.'),
            ]);
        }

        $decision->update([
            'decision' => $decisionValue,
            'homeroom_note' => $note,
            'to_classroom_id' => $toClassroomId ?? $decision->to_classroom_id,
            'decided_by' => $reviewer->id,
        ]);

        $batch = $decision->batch;
        if ($batch && $batch->status === PromotionBatch::STATUS_DRAFT) {
            $batch->update(['status' => PromotionBatch::STATUS_HOMEROOM_REVIEW]);
        }

        return $decision->refresh();
    }

    /**
     * Waka Kurikulum finalizes the promotion batch (`ditetapkan`).
     * Blocks if any Grade X student marked `naik` has not been plotted into a Grade XI classroom.
     */
    public function decideBatch(PromotionBatch $batch, User $waka): PromotionBatch
    {
        if (! $waka->hasPermission('kurikulum.tetapkan_kenaikan')) {
            abort(403, __('Hanya Waka Kurikulum yang berwenang menetapkan kenaikan kelas.'));
        }

        $decisions = $batch->decisions()->with(['student.user', 'fromClassroom'])->get();
        $undecided = [];
        $unplacedGradeX = [];

        foreach ($decisions as $d) {
            if ($d->decision === null) {
                $undecided[] = $d->student?->user?->name ?? "Siswa #{$d->student_id}";
                continue;
            }

            if ($d->decision === 'naik') {
                $targetClassroomId = $d->to_classroom_id ?? ClassPlacement::query()
                    ->where('school_year_id', $batch->to_school_year_id)
                    ->where('student_id', $d->student_id)
                    ->value('classroom_id');

                if ($targetClassroomId !== null && $d->to_classroom_id === null) {
                    $d->update(['to_classroom_id' => $targetClassroomId]);
                }

                $grade = (string) ($d->fromClassroom?->grade_level ?? '');
                if (in_array($grade, ['X', '10'], true) && $targetClassroomId === null) {
                    $unplacedGradeX[] = $d->student?->user?->name ?? "Siswa #{$d->student_id}";
                }
            }
        }

        if ($undecided !== []) {
            throw ValidationException::withMessages([
                'decisions' => __('Masih ada siswa yang belum memiliki keputusan kenaikan: :names', [
                    'names' => implode(', ', $undecided),
                ]),
            ]);
        }

        if ($unplacedGradeX !== []) {
            throw ValidationException::withMessages([
                'placements' => __('Siswa kelas X yang naik wajib diplot ke rombel XI terlebih dahulu: :names', [
                    'names' => implode(', ', $unplacedGradeX),
                ]),
            ]);
        }

        $batch->update([
            'status' => PromotionBatch::STATUS_DECIDED,
            'decided_by' => $waka->id,
            'decided_at' => now(),
            'held_by' => null,
            'held_at' => null,
            'hold_reason' => null,
        ]);

        return $batch->refresh();
    }

    /**
     * Principal holds (`menahan`) a decided promotion batch before execution,
     * returning it to `ditinjau_wali` with a recorded note.
     */
    public function holdBatch(PromotionBatch $batch, User $principal, string $reason): PromotionBatch
    {
        if (! $principal->hasPermission('pimpinan.mengesahkan')) {
            abort(403, __('Hanya Kepala Sekolah yang dapat menahan penetapan kenaikan kelas.'));
        }

        if ($batch->status === PromotionBatch::STATUS_EXECUTED) {
            throw ValidationException::withMessages([
                'status' => __('Batch kenaikan yang sudah dieksekusi tidak dapat ditahan.'),
            ]);
        }

        $batch->update([
            'status' => PromotionBatch::STATUS_HOMEROOM_REVIEW,
            'held_by' => $principal->id,
            'held_at' => now(),
            'hold_reason' => $reason,
        ]);

        return $batch->refresh();
    }

    /**
     * Atomically execute a decided promotion batch (idempotent).
     */
    public function executeBatch(PromotionBatch $batch, User $actor): PromotionBatch
    {
        if (! $actor->hasPermission('kurikulum.tetapkan_kenaikan') && ! $actor->hasPermission('pimpinan.mengesahkan')) {
            abort(403, __('Anda tidak berwenang mengeksekusi batch kenaikan kelas.'));
        }

        if ($batch->status === PromotionBatch::STATUS_EXECUTED) {
            return $batch;
        }

        if ($batch->status !== PromotionBatch::STATUS_DECIDED || $batch->held_at !== null) {
            throw ValidationException::withMessages([
                'status' => __('Batch kenaikan harus berstatus ditetapkan dan tidak sedang ditahan Kepala Sekolah sebelum dieksekusi.'),
            ]);
        }

        return DB::transaction(function () use ($batch, $actor) {
            $lockedBatch = PromotionBatch::query()->lockForUpdate()->findOrFail($batch->id);
            if ($lockedBatch->status === PromotionBatch::STATUS_EXECUTED) {
                return $lockedBatch;
            }

            $toYear = SchoolYear::findOrFail($lockedBatch->to_school_year_id);
            $startDate = $toYear->start_date ?? CarbonImmutable::today();

            $decisions = $lockedBatch->decisions()->with(['student.user', 'toClassroom'])->get();

            foreach ($decisions as $decision) {
                $student = $decision->student;
                if (! $student) {
                    continue;
                }

                $targetClassroomId = $decision->to_classroom_id ?? ClassPlacement::query()
                    ->where('school_year_id', $toYear->id)
                    ->where('student_id', $student->id)
                    ->value('classroom_id');

                // Close enrollment in from_school_year_id with exact decision status
                ClassEnrollment::query()
                    ->where('student_id', $student->id)
                    ->where('school_year_id', $lockedBatch->from_school_year_id)
                    ->where('status', ClassEnrollment::STATUS_ACTIVE)
                    ->update([
                        'status' => $decision->decision,
                        'ended_on' => CarbonImmutable::instance($startDate)->toDateString(),
                        'ended_reason' => 'Keputusan rapat kenaikan/kelulusan',
                        'decided_by' => $actor->id,
                        'promotion_decision_id' => $decision->id,
                    ]);

                if (in_array($decision->decision, ['naik', 'tinggal'], true) && $targetClassroomId !== null) {
                    $targetClassroom = Classroom::findOrFail($targetClassroomId);
                    $this->enroll(
                        $student,
                        $targetClassroom,
                        $toYear,
                        $startDate,
                        $actor,
                        $decision->id
                    );
                } elseif ($decision->decision === 'lulus') {
                    $student->update(['status' => 'graduated']);
                    if ($student->user !== null) {
                        $student->user->update([
                            'account_status' => 'nonaktif',
                            'deactivated_reason' => 'lulus',
                        ]);
                    }
                }
            }

            $lockedBatch->update([
                'status' => PromotionBatch::STATUS_EXECUTED,
                'executed_at' => now(),
            ]);

            return $lockedBatch->refresh();
        });
    }

    /**
     * Cancel an executed or decided batch before the target school year is activated.
     */
    public function cancelBatch(PromotionBatch $batch, User $actor): PromotionBatch
    {
        if (! $actor->hasPermission('kurikulum.tetapkan_kenaikan')) {
            abort(403);
        }

        $toYear = $batch->toSchoolYear;
        if ($toYear && $toYear->status === SchoolYear::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'status' => __('Batch kenaikan tidak dapat dibatalkan setelah Tahun Ajaran baru diaktifkan.'),
            ]);
        }

        return DB::transaction(function () use ($batch) {
            if ($batch->status === PromotionBatch::STATUS_EXECUTED) {
                $decisions = $batch->decisions()->with('student')->get();
                foreach ($decisions as $decision) {
                    ClassEnrollment::query()
                        ->where('student_id', $decision->student_id)
                        ->where('school_year_id', $batch->to_school_year_id)
                        ->delete();

                    ClassEnrollment::query()
                        ->where('student_id', $decision->student_id)
                        ->where('school_year_id', $batch->from_school_year_id)
                        ->update([
                            'status' => ClassEnrollment::STATUS_ACTIVE,
                            'ended_on' => null,
                            'ended_reason' => null,
                        ]);

                    if ($decision->student) {
                        $decision->student->update([
                            'classroom_id' => $decision->from_classroom_id,
                            'status' => 'active',
                        ]);
                    }
                }
            }

            $batch->update([
                'status' => PromotionBatch::STATUS_CANCELLED,
            ]);

            return $batch->refresh();
        });
    }
}
