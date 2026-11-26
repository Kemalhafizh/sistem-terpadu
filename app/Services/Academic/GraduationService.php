<?php

namespace App\Services\Academic;

use App\Http\Middleware\RoleMiddleware;
use App\Models\AlumniData;
use App\Models\Attendance;
use App\Models\ClassEnrollment;
use App\Models\Classroom;
use App\Models\DapodikSyncItem;
use App\Models\Grade;
use App\Models\GraduationBatch;
use App\Models\GraduationDecision;
use App\Models\MeetingNote;
use App\Models\ReportCard;
use App\Models\SchoolYear;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowRequest;
use App\Notifications\GraduationAnnouncedNotification;
use App\Services\WorkflowService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GraduationService
{
    public const WORKFLOW_CODE = 'graduation_approval';

    public function __construct(
        private readonly WorkflowService $workflowService,
    ) {}

    public function canManageGraduation(User $user): bool
    {
        return $user->hasPermission('kurikulum.tetapkan_kenaikan')
            || $user->hasPermission('akademik.kelola_struktur')
            || RoleMiddleware::allowsUser($user, ['waka_kurikulum', 'admin', 'superadmin']);
    }

    public function ensureWorkflowDefinition(): WorkflowDefinition
    {
        return WorkflowDefinition::firstOrCreate(
            ['code' => self::WORKFLOW_CODE],
            [
                'name' => 'Pengesahan SK Kelulusan Kelas XII',
                'description' => 'Alur pengesahan SK kelulusan massal satu angkatan kelas XII oleh Kepala Sekolah berdasarkan rapat pleno dewan guru.',
                'steps' => [
                    ['order' => 1, 'role' => 'kepala_sekolah', 'sla_hours' => 48],
                ],
                'is_active' => true,
            ]
        );
    }

    /**
     * Draft or sync a Grade XII graduation batch for a school year (`ALUR_KERJA.md` A5 step 1).
     * Never fabricates averages or automatic decisions when grades/thresholds are absent.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function createOrSyncBatch(SchoolYear $schoolYear, User $actor, array $attributes = []): GraduationBatch
    {
        if (! $this->canManageGraduation($actor)) {
            abort(403, __('Hanya Waka Kurikulum atau Admin Akademik yang berwenang menyusun draf kelulusan.'));
        }

        return DB::transaction(function () use ($schoolYear, $actor, $attributes) {
            $milestoneDate = $schoolYear->milestoneDate('kelulusan');
            $graduationDate = isset($attributes['graduation_date']) && filled($attributes['graduation_date'])
                ? CarbonImmutable::parse((string) $attributes['graduation_date'])->toDateString()
                : $milestoneDate?->toDateString();

            $graduationYear = isset($attributes['graduation_year']) && is_numeric($attributes['graduation_year'])
                ? (int) $attributes['graduation_year']
                : $this->inferGraduationYear($schoolYear, $graduationDate);

            $batch = GraduationBatch::query()->firstOrCreate(
                ['school_year_id' => $schoolYear->id],
                [
                    'graduation_year' => $graduationYear,
                    'sk_number' => $attributes['sk_number'] ?? null,
                    'sk_date' => $attributes['sk_date'] ?? null,
                    'graduation_date' => $graduationDate,
                    'meeting_note_id' => $attributes['meeting_note_id'] ?? null,
                    'status' => GraduationBatch::STATUS_DRAFT,
                    'created_by' => $actor->id,
                    'notes' => $attributes['notes'] ?? null,
                ]
            );

            if (in_array($batch->status, [GraduationBatch::STATUS_DRAFT, GraduationBatch::STATUS_REVISION], true)) {
                $updates = [];
                foreach (['graduation_year', 'sk_number', 'sk_date', 'graduation_date', 'meeting_note_id', 'notes'] as $field) {
                    if (array_key_exists($field, $attributes) && $attributes[$field] !== null) {
                        $updates[$field] = $attributes[$field];
                    }
                }
                if ($updates !== []) {
                    $batch->update($updates);
                }
            }

            $semesterIds = $schoolYear->semesters()->pluck('id')->all();

            $minAvgSetting = Setting::get('graduation_min_average_score');
            $maxBelowKktpSetting = Setting::get('graduation_max_below_kktp');
            $hasThresholds = is_numeric($minAvgSetting) && is_numeric($maxBelowKktpSetting);

            $candidates = $this->collectGrade12Candidates($schoolYear);

            foreach ($candidates as ['student' => $student, 'classroom_id' => $classroomId]) {
                $basis = $this->buildAcademicBasis($student, $semesterIds, $hasThresholds);

                $autoDecision = null;
                if ($hasThresholds && $basis['report_card_average'] !== null && $basis['below_kktp_count'] !== null) {
                    $passesAvg = (float) $basis['report_card_average'] >= (float) $minAvgSetting;
                    $passesKktp = (int) $basis['below_kktp_count'] <= (int) $maxBelowKktpSetting;
                    $autoDecision = ($passesAvg && $passesKktp)
                        ? GraduationDecision::DECISION_GRADUATED
                        : GraduationDecision::DECISION_NOT_GRADUATED;
                }

                GraduationDecision::query()->firstOrCreate(
                    [
                        'graduation_batch_id' => $batch->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'classroom_id' => $classroomId,
                        'decision' => $autoDecision,
                        'basis' => $basis,
                        'decided_by' => $autoDecision !== null ? $actor->id : null,
                    ]
                );
            }

            return $batch->load(['decisions.student.user', 'decisions.classroom', 'meetingNote', 'schoolYear']);
        });
    }

    /**
     * Record or update a single student's graduation decision.
     */
    public function updateDecision(
        GraduationDecision $decision,
        User $actor,
        string $decisionValue,
        ?string $note = null,
    ): GraduationDecision {
        $decision->loadMissing(['batch', 'classroom']);
        $batch = $decision->batch;
        if (! $batch || ! in_array($batch->status, [GraduationBatch::STATUS_DRAFT, GraduationBatch::STATUS_REVISION], true)) {
            throw ValidationException::withMessages([
                'status' => __('Keputusan siswa hanya dapat diubah ketika draf kelulusan berstatus draf atau revisi.'),
            ]);
        }

        $classroom = $decision->classroom;
        $isHomeroom = $classroom !== null && (int) $classroom->teacher_id === (int) $actor->id;

        if (! $isHomeroom && ! $this->canManageGraduation($actor)) {
            abort(403, __('Hanya wali kelas XII terkait atau Waka Kurikulum yang dapat memperbarui keputusan kelulusan siswa ini.'));
        }

        if (! in_array($decisionValue, [GraduationDecision::DECISION_GRADUATED, GraduationDecision::DECISION_NOT_GRADUATED], true)) {
            throw ValidationException::withMessages([
                'decision' => __('Keputusan kelulusan harus berupa lulus atau tidak_lulus.'),
            ]);
        }

        $decision->update([
            'decision' => $decisionValue,
            'note' => $note,
            'decided_by' => $actor->id,
        ]);

        return $decision->refresh();
    }

    /**
     * Bulk update graduation decisions across a cohort (`ALUR_KERJA.md` 1b rule 4).
     *
     * @param  array<int, array{student_id: int, decision: string, note?: ?string}|string>  $decisionsPayload
     */
    public function bulkUpdateDecisions(GraduationBatch $batch, User $actor, array $decisionsPayload): GraduationBatch
    {
        if (! $this->canManageGraduation($actor)) {
            abort(403, __('Hanya Waka Kurikulum atau Admin Akademik yang dapat memperbarui keputusan kelulusan secara massal.'));
        }

        if (! in_array($batch->status, [GraduationBatch::STATUS_DRAFT, GraduationBatch::STATUS_REVISION], true)) {
            throw ValidationException::withMessages([
                'status' => __('Batch kelulusan yang sudah diajukan atau disahkan tidak dapat diubah sebelum dikembalikan untuk revisi.'),
            ]);
        }

        return DB::transaction(function () use ($batch, $actor, $decisionsPayload) {
            foreach ($decisionsPayload as $key => $entry) {
                $studentId = is_array($entry) ? (int) ($entry['student_id'] ?? $key) : (int) $key;
                $decisionValue = is_array($entry) ? (string) ($entry['decision'] ?? '') : (string) $entry;
                $note = is_array($entry) ? ($entry['note'] ?? null) : null;

                if (! in_array($decisionValue, [GraduationDecision::DECISION_GRADUATED, GraduationDecision::DECISION_NOT_GRADUATED], true)) {
                    throw ValidationException::withMessages([
                        'decisions' => __('Keputusan kelulusan harus berupa lulus atau tidak_lulus.'),
                    ]);
                }

                $decision = GraduationDecision::query()
                    ->where('graduation_batch_id', $batch->id)
                    ->where('student_id', $studentId)
                    ->first();

                if ($decision) {
                    $decision->update([
                        'decision' => $decisionValue,
                        'note' => $note ?? $decision->note,
                        'decided_by' => $actor->id,
                    ]);
                }
            }

            return $batch->fresh(['decisions.student.user', 'decisions.classroom']);
        });
    }

    /**
     * Submit cohort graduation batch to Kepala Sekolah via `WorkflowService` (`graduation_approval`),
     * requiring complete decisions and a finalized plenary meeting note (`J8`).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function submitForApproval(GraduationBatch $batch, User $requester, array $attributes = []): WorkflowRequest
    {
        if (! $this->canManageGraduation($requester)) {
            abort(403, __('Hanya Waka Kurikulum atau Admin Akademik yang dapat mengajukan pengesahan kelulusan.'));
        }

        if (! in_array($batch->status, [GraduationBatch::STATUS_DRAFT, GraduationBatch::STATUS_REVISION], true)) {
            throw ValidationException::withMessages([
                'status' => __('Hanya batch kelulusan berstatus draf atau revisi yang dapat diajukan ke Kepala Sekolah.'),
            ]);
        }

        return DB::transaction(function () use ($batch, $requester, $attributes) {
            $updates = [];
            foreach (['meeting_note_id', 'sk_number', 'sk_date', 'graduation_date', 'notes'] as $field) {
                if (array_key_exists($field, $attributes) && $attributes[$field] !== null) {
                    $updates[$field] = $attributes[$field];
                }
            }
            if ($updates !== []) {
                $batch->update($updates);
            }

            $decisions = $batch->decisions()->with('student.user')->get();
            if ($decisions->isEmpty()) {
                throw ValidationException::withMessages([
                    'decisions' => __('Batch kelulusan belum memiliki daftar siswa kelas XII.'),
                ]);
            }

            $undecided = $decisions
                ->filter(fn (GraduationDecision $d) => $d->decision === null)
                ->map(fn (GraduationDecision $d) => $d->student?->user?->name ?? "Siswa #{$d->student_id}")
                ->values()
                ->all();

            if ($undecided !== []) {
                throw ValidationException::withMessages([
                    'decisions' => __('Masih ada siswa kelas XII yang belum memiliki keputusan rapat pleno: :names', [
                        'names' => implode(', ', $undecided),
                    ]),
                ]);
            }

            $meetingNote = $batch->meeting_note_id ? MeetingNote::find($batch->meeting_note_id) : null;
            if (! $meetingNote || $meetingNote->status !== 'finalized') {
                throw ValidationException::withMessages([
                    'meeting_note_id' => __('Pengajuan pengesahan kelulusan wajib merujuk ke Notulen Rapat Dewan Guru (J8) yang telah difinalisasi.'),
                ]);
            }

            $this->ensureWorkflowDefinition();

            $existingRevisionRequest = $batch->workflowRequests()
                ->where('status', 'revision')
                ->latest('id')
                ->first();

            $batch->update([
                'submitted_by' => $requester->id,
                'submitted_at' => now(),
            ]);

            if ($existingRevisionRequest) {
                $this->workflowService->resubmit($existingRevisionRequest, $requester, $batch->notes);

                return $existingRevisionRequest->fresh();
            }

            return $this->workflowService->initiate(
                self::WORKFLOW_CODE,
                $requester,
                $batch,
                $batch->notes
            );
        });
    }

    /**
     * Kepala Sekolah approves the cohort graduation SK (`ALUR_KERJA.md` A5 step 3).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function approveBatch(GraduationBatch $batch, User $approver, array $attributes = []): GraduationBatch
    {
        if (! $approver->hasPermission('pimpinan.mengesahkan') && ! RoleMiddleware::allowsUser($approver, ['kepala_sekolah', 'kepsek', 'superadmin'])) {
            abort(403, __('Hanya Kepala Sekolah yang berwenang mengesahkan SK kelulusan.'));
        }

        if ($batch->submitted_by !== null && (int) $batch->submitted_by === (int) $approver->id) {
            abort(403, __('Penyusun pengajuan tidak dapat mengesahkan draf kelulusannya sendiri.'));
        }

        if ($batch->status !== GraduationBatch::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'status' => __('Batch kelulusan harus berstatus menunggu pengesahan (pending) sebelum dapat disahkan.'),
            ]);
        }

        $skNumber = trim((string) ($attributes['sk_number'] ?? $batch->sk_number ?? ''));
        if ($skNumber === '') {
            throw ValidationException::withMessages([
                'sk_number' => __('Nomor SK Kelulusan wajib diisi saat pengesahan oleh Kepala Sekolah.'),
            ]);
        }

        return DB::transaction(function () use ($batch, $approver, $attributes, $skNumber) {
            $batch->update([
                'sk_number' => $skNumber,
                'sk_date' => $attributes['sk_date'] ?? $batch->sk_date ?? CarbonImmutable::today()->toDateString(),
                'graduation_date' => $attributes['graduation_date'] ?? $batch->graduation_date ?? CarbonImmutable::today()->toDateString(),
            ]);

            $activeWorkflow = $batch->workflowRequests()
                ->whereIn('status', ['pending', 'in_review'])
                ->latest('id')
                ->first();

            if ($activeWorkflow) {
                $this->workflowService->approve($activeWorkflow, $approver, $attributes['remarks'] ?? null);
            } else {
                $this->onWorkflowApproved($batch, $approver);
            }

            return $batch->refresh();
        });
    }

    /**
     * Callback invoked when `WorkflowService::approve()` completes `graduation_approval`.
     */
    public function onWorkflowApproved(GraduationBatch $batch, User $approver): void
    {
        $batch->loadMissing('schoolYear.calendar.events');

        $batch->update([
            'status' => GraduationBatch::STATUS_APPROVED,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'sk_date' => $batch->sk_date ?? CarbonImmutable::today()->toDateString(),
            'graduation_date' => $batch->graduation_date
                ?? $batch->schoolYear?->milestoneDate('kelulusan')?->toDateString()
                ?? CarbonImmutable::today()->toDateString(),
        ]);
    }

    /**
     * Announce and atomically execute an approved GraduationBatch (`ALUR_KERJA.md` A5 steps 4-6):
     * - Closes active `ClassEnrollment` as `lulus`
     * - Transitions graduated students to `status = 'alumni'`
     * - Creates `AlumniData` (ready for K3 Tracer Study & K2 Buku Induk)
     * - Queues K1 Dapodik sync item and notifies students & parents
     *
     * @param  array<string, mixed>  $attributes
     */
    public function executeAndAnnounce(GraduationBatch $batch, User $actor, array $attributes = []): GraduationBatch
    {
        if (! $this->canManageGraduation($actor) && ! $actor->hasPermission('pimpinan.mengesahkan')) {
            abort(403, __('Anda tidak berwenang mengeksekusi dan mengumumkan kelulusan.'));
        }

        if ($batch->status === GraduationBatch::STATUS_EXECUTED) {
            return $batch;
        }

        if ($batch->status !== GraduationBatch::STATUS_APPROVED) {
            throw ValidationException::withMessages([
                'status' => __('Batch kelulusan wajib disahkan Kepala Sekolah terlebih dahulu sebelum diumumkan dan dieksekusi.'),
            ]);
        }

        return DB::transaction(function () use ($batch, $actor, $attributes) {
            $locked = GraduationBatch::query()->lockForUpdate()->findOrFail($batch->id);
            if ($locked->status === GraduationBatch::STATUS_EXECUTED) {
                return $locked;
            }

            $locked->loadMissing('schoolYear.calendar.events');

            $skNumber = trim((string) ($attributes['sk_number'] ?? $locked->sk_number ?? ''));
            if ($skNumber === '') {
                throw ValidationException::withMessages([
                    'sk_number' => __('Nomor SK Kelulusan Kepala Sekolah wajib tercatat sebelum kelulusan dieksekusi.'),
                ]);
            }

            $effectiveDate = isset($attributes['graduation_date']) && filled($attributes['graduation_date'])
                ? CarbonImmutable::parse((string) $attributes['graduation_date'])->toDateString()
                : ($locked->graduation_date?->toDateString()
                    ?? $locked->schoolYear?->milestoneDate('kelulusan')?->toDateString()
                    ?? CarbonImmutable::today()->toDateString());

            $locked->update([
                'sk_number' => $skNumber,
                'sk_date' => $attributes['sk_date'] ?? $locked->sk_date?->toDateString() ?? $effectiveDate,
                'graduation_date' => $effectiveDate,
            ]);

            $decisions = $locked->decisions()->with(['student.user', 'student.parent', 'student.alumniData', 'classroom'])->get();

            foreach ($decisions as $decision) {
                $student = $decision->student;
                if (! $student) {
                    continue;
                }

                if ($decision->decision === GraduationDecision::DECISION_GRADUATED) {
                    $previousStatus = (string) $student->status;

                    ClassEnrollment::query()
                        ->where('student_id', $student->id)
                        ->where('status', ClassEnrollment::STATUS_ACTIVE)
                        ->update([
                            'status' => ClassEnrollment::STATUS_GRADUATED,
                            'ended_on' => $effectiveDate,
                            'ended_reason' => 'Lulus berdasarkan SK '.$skNumber,
                            'decided_by' => $locked->approved_by ?? $actor->id,
                        ]);

                    $student->update(['status' => 'alumni']);

                    AlumniData::updateOrCreate(
                        ['student_id' => $student->id],
                        [
                            'graduation_batch_id' => $locked->id,
                            'graduation_year' => $locked->graduation_year,
                            'sk_number' => $skNumber,
                            'graduation_date' => $effectiveDate,
                            'current_activity' => $student->alumniData?->current_activity ?? 'other',
                            'email' => $student->alumniData?->email ?? $student->user?->email,
                            'phone_number' => $student->alumniData?->phone_number ?? $student->user?->phone_number,
                        ]
                    );

                    DapodikSyncItem::firstOrCreate(
                        [
                            'entity_type' => DapodikSyncItem::ENTITY_STUDENT,
                            'entity_id' => $student->id,
                            'field_name' => 'status',
                            'new_value' => 'alumni (SK '.$skNumber.')',
                        ],
                        [
                            'dapodik_column_label' => 'Kelulusan Peserta Didik Kelas XII',
                            'old_value' => $previousStatus,
                            'status' => DapodikSyncItem::STATUS_PENDING,
                            'notes' => 'Lulus Tahun '.$locked->graduation_year.' berdasarkan SK '.$skNumber,
                        ]
                    );
                }

                $notification = new GraduationAnnouncedNotification($locked, $student, (string) $decision->decision);
                if ($student->user) {
                    $student->user->notify($notification);
                }
                if ($student->parent) {
                    $student->parent->notify($notification);
                }
            }

            $locked->update([
                'status' => GraduationBatch::STATUS_EXECUTED,
                'announced_at' => now(),
                'executed_by' => $actor->id,
                'executed_at' => now(),
            ]);

            return $locked->refresh();
        });
    }

    private function inferGraduationYear(SchoolYear $schoolYear, ?string $graduationDate): int
    {
        if ($graduationDate !== null) {
            return CarbonImmutable::parse($graduationDate)->year;
        }

        if ($schoolYear->end_date !== null) {
            return (int) $schoolYear->end_date->format('Y');
        }

        if (preg_match('/(\d{4})\s*$/', (string) $schoolYear->name, $matches) === 1) {
            return (int) $matches[1];
        }

        return (int) date('Y');
    }

    /**
     * Collect Grade XII students from active `ClassEnrollment` in `$schoolYear`,
     * falling back to active `Student` rows in Grade XII classrooms of `$schoolYear`.
     *
     * @return list<array{student: Student, classroom_id: ?int}>
     */
    private function collectGrade12Candidates(SchoolYear $schoolYear): array
    {
        $byStudentId = [];

        $enrollments = ClassEnrollment::query()
            ->where('school_year_id', $schoolYear->id)
            ->where('status', ClassEnrollment::STATUS_ACTIVE)
            ->with(['student', 'classroom'])
            ->get();

        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;
            $classroom = $enrollment->classroom;
            if (! $student || ! $classroom || ! $this->isGrade12Classroom($classroom)) {
                continue;
            }
            $byStudentId[$student->id] = [
                'student' => $student,
                'classroom_id' => $classroom->id,
            ];
        }

        $grade12Classrooms = Classroom::query()
            ->where(function ($q) use ($schoolYear) {
                $q->where('school_year_id', $schoolYear->id)
                    ->orWhere('academic_year', $schoolYear->name);
            })
            ->get()
            ->filter(fn (Classroom $c) => $this->isGrade12Classroom($c));

        if ($grade12Classrooms->isNotEmpty()) {
            $fallbackStudents = Student::query()
                ->whereIn('classroom_id', $grade12Classrooms->pluck('id'))
                ->where('status', 'active')
                ->get();

            foreach ($fallbackStudents as $student) {
                if (! isset($byStudentId[$student->id])) {
                    $byStudentId[$student->id] = [
                        'student' => $student,
                        'classroom_id' => $student->classroom_id,
                    ];
                }
            }
        }

        return array_values($byStudentId);
    }

    private function isGrade12Classroom(Classroom $classroom): bool
    {
        $level = strtoupper(trim((string) $classroom->grade_level));
        if (in_array($level, ['XII', '12'], true)) {
            return true;
        }

        $name = strtoupper(trim((string) $classroom->name));

        return str_starts_with($name, 'XII') || str_starts_with($name, '12');
    }

    /**
     * Build honest academic basis from real database rows (`null` when unrecorded).
     *
     * @param  list<int>  $semesterIds
     * @return array{report_card_average: ?float, completed_semesters: int, below_kktp_count: ?int, alpha_days: ?int, thresholds_configured: bool}
     */
    private function buildAcademicBasis(Student $student, array $semesterIds, bool $hasThresholds): array
    {
        $reportCards = ReportCard::query()
            ->where('student_id', $student->id)
            ->whereNotNull('average_score')
            ->get(['academic_year_id', 'average_score']);

        $grades = Grade::query()
            ->with('subject:id,kkm')
            ->where('student_id', $student->id)
            ->get(['academic_year_id', 'subject_id', 'score']);

        $reportCardAverage = null;
        if ($reportCards->isNotEmpty()) {
            $reportCardAverage = round((float) $reportCards->avg('average_score'), 2);
        } elseif ($grades->isNotEmpty()) {
            $reportCardAverage = round((float) $grades->avg('score'), 2);
        }

        $completedSemesters = $reportCards->pluck('academic_year_id')
            ->merge($grades->pluck('academic_year_id'))
            ->filter()
            ->unique()
            ->count();

        $yearGrades = $semesterIds !== []
            ? $grades->whereIn('academic_year_id', $semesterIds)
            : $grades;

        $belowKktpCount = null;
        if ($yearGrades->isNotEmpty()) {
            $belowKktpCount = $yearGrades
                ->groupBy('subject_id')
                ->filter(function ($subjectGrades) {
                    $kkm = (float) ($subjectGrades->first()?->subject?->kkm ?? 75);

                    return (float) $subjectGrades->avg('score') < $kkm;
                })
                ->count();
        }

        $alphaDays = null;
        if ($semesterIds !== []) {
            $attendanceRows = Attendance::query()
                ->where('student_id', $student->id)
                ->whereIn('academic_year_id', $semesterIds)
                ->count();

            if ($attendanceRows > 0) {
                $alphaDays = Attendance::query()
                    ->where('student_id', $student->id)
                    ->whereIn('academic_year_id', $semesterIds)
                    ->where('status', 'A')
                    ->count();
            }
        }

        return [
            'report_card_average' => $reportCardAverage,
            'completed_semesters' => $completedSemesters,
            'below_kktp_count' => $belowKktpCount,
            'alpha_days' => $alphaDays,
            'thresholds_configured' => $hasThresholds,
        ];
    }
}
