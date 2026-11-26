<?php

namespace App\Services;

use App\Events\NewNotificationEvent;
use App\Http\Middleware\RoleMiddleware;
use App\Models\AcademicYear;
use App\Models\Billing;
use App\Models\ClassEnrollment;
use App\Models\Classroom;
use App\Models\DapodikSyncItem;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentMutation;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Services\Academic\EnrollmentService;
use App\Services\Auth\AccountProvisioningService;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentMutationService
{
    public const WORKFLOW_CODE = 'mutation_approval';

    public function __construct(
        private readonly WorkflowService $workflowService,
        private readonly EnrollmentService $enrollmentService,
        private readonly AccountProvisioningService $accountProvisioningService,
    ) {}

    public function ensureWorkflowDefinition(): WorkflowDefinition
    {
        return WorkflowDefinition::firstOrCreate(
            ['code' => self::WORKFLOW_CODE],
            [
                'name' => 'Persetujuan Mutasi Siswa',
                'description' => 'Alur persetujuan permohonan mutasi siswa masuk atau keluar oleh Kepala Sekolah.',
                'steps' => [
                    ['order' => 1, 'role' => 'kepala_sekolah', 'sla_hours' => 48],
                ],
                'is_active' => true,
            ]
        );
    }

    /**
     * Record a new student mutation request (`in` or `out`), upload private supporting
     * document, snapshot billing status for `out`, notify Waka Kesiswaan ("Mengetahui"),
     * and submit to Kepala Sekolah via `WorkflowService` (`ALUR_KERJA.md` A6 steps 1-2).
     *
     * @param  array<string, mixed>  $validatedData
     */
    public function processMutation(
        array $validatedData,
        ?UploadedFile $document = null,
        ?User $requester = null,
    ): StudentMutation {
        $actor = $requester ?? auth()->user();

        return DB::transaction(function () use ($validatedData, $document, $actor) {
            $type = (string) $validatedData['type'];
            $docPath = $document ? $document->store('mutations', 'local') : null;

            $student = ! empty($validatedData['student_id'])
                ? Student::with(['user', 'classroom'])->findOrFail($validatedData['student_id'])
                : null;

            if ($type === StudentMutation::TYPE_OUT && ! $student) {
                throw ValidationException::withMessages([
                    'student_id' => __('Siswa wajib dipilih untuk permohonan mutasi keluar.'),
                ]);
            }

            $classroomId = isset($validatedData['classroom_id']) && filled($validatedData['classroom_id'])
                ? (int) $validatedData['classroom_id']
                : $student?->classroom_id;

            if ($type === StudentMutation::TYPE_IN && ! $classroomId) {
                throw ValidationException::withMessages([
                    'classroom_id' => __('Rombel tujuan wajib dipilih untuk permohonan mutasi masuk.'),
                ]);
            }

            $schoolYearId = isset($validatedData['school_year_id']) && filled($validatedData['school_year_id'])
                ? (int) $validatedData['school_year_id']
                : $this->resolveSchoolYear($classroomId)?->id;

            $candidateData = null;
            if ($type === StudentMutation::TYPE_IN && ! $student) {
                $candidateData = $this->extractCandidateData($validatedData);
                if (blank($candidateData['name'] ?? null) || blank($candidateData['nis'] ?? null)) {
                    throw ValidationException::withMessages([
                        'student_name' => __('Nama lengkap dan NIS calon siswa pindahan wajib diisi untuk mutasi masuk.'),
                    ]);
                }
            }

            $billingStatus = null;
            $billingSummary = null;
            if ($type === StudentMutation::TYPE_OUT && $student) {
                [$billingStatus, $billingSummary] = $this->evaluateStudentBillings($student, (string) $validatedData['mutation_date']);
            }

            $mutation = StudentMutation::create([
                'student_id' => $student?->id,
                'classroom_id' => $classroomId,
                'school_year_id' => $schoolYearId,
                'type' => $type,
                'mutation_date' => CarbonImmutable::parse((string) $validatedData['mutation_date'])->toDateString(),
                'reason' => (string) $validatedData['reason'],
                'target_school' => $validatedData['target_school'] ?? null,
                'letter_number' => $validatedData['letter_number'] ?? null,
                'document_path' => $docPath,
                'status' => StudentMutation::STATUS_PENDING,
                'candidate_data' => $candidateData,
                'billing_review_status' => $billingStatus,
                'billing_review_summary' => $billingSummary,
                'requested_by' => $actor?->id,
            ]);

            if ($actor) {
                $this->ensureWorkflowDefinition();
                $this->workflowService->initiate(
                    self::WORKFLOW_CODE,
                    $actor,
                    $mutation,
                    $mutation->reason
                );
            }

            $this->notifyWakaKesiswaan($mutation, 'submitted');

            return $mutation->fresh(['student.user', 'classroom', 'schoolYear']);
        });
    }

    /**
     * Kepala Sekolah approves a pending student mutation (`ALUR_KERJA.md` A6 steps 2-3).
     */
    public function approveMutation(
        StudentMutation $mutation,
        User $approver,
        ?string $remarks = null,
        ?string $letterNumber = null,
    ): StudentMutation {
        if (! $approver->hasPermission('pimpinan.mengesahkan') && ! RoleMiddleware::allowsUser($approver, ['kepala_sekolah', 'kepsek', 'superadmin'])) {
            abort(403, __('Hanya Kepala Sekolah yang berwenang menyetujui permohonan mutasi siswa.'));
        }

        if ($mutation->requested_by !== null && (int) $mutation->requested_by === (int) $approver->id) {
            abort(403, __('Pengaju permohonan tidak dapat menyetujui mutasinya sendiri.'));
        }

        if ($mutation->executed_at !== null && $mutation->status === StudentMutation::STATUS_APPROVED) {
            return $mutation;
        }

        if (! in_array($mutation->status, [StudentMutation::STATUS_PENDING, 'in_review'], true)) {
            throw ValidationException::withMessages([
                'status' => __('Permohonan mutasi ini tidak sedang menunggu persetujuan.'),
            ]);
        }

        return DB::transaction(function () use ($mutation, $approver, $remarks, $letterNumber) {
            if (filled($letterNumber)) {
                $mutation->update(['letter_number' => trim((string) $letterNumber)]);
            }

            $activeWorkflow = $mutation->workflowRequests()
                ->whereIn('status', ['pending', 'in_review'])
                ->latest('id')
                ->first();

            if ($activeWorkflow) {
                // WorkflowService::approve() invokes applyApprovedMutation() atomically.
                $this->workflowService->approve($activeWorkflow, $approver, $remarks);
            } else {
                $this->applyApprovedMutation($mutation, $approver);
            }

            return $mutation->refresh();
        });
    }

    /**
     * Kepala Sekolah rejects a pending student mutation.
     */
    public function rejectMutation(StudentMutation $mutation, User $approver, string $reason): StudentMutation
    {
        if (! $approver->hasPermission('pimpinan.mengesahkan') && ! RoleMiddleware::allowsUser($approver, ['kepala_sekolah', 'kepsek', 'superadmin'])) {
            abort(403, __('Hanya Kepala Sekolah yang berwenang menolak permohonan mutasi siswa.'));
        }

        return DB::transaction(function () use ($mutation, $approver, $reason) {
            $activeWorkflow = $mutation->workflowRequests()
                ->whereIn('status', ['pending', 'in_review'])
                ->latest('id')
                ->first();

            if ($activeWorkflow) {
                $this->workflowService->reject($activeWorkflow, $approver, $reason);
            }

            $mutation->update([
                'status' => StudentMutation::STATUS_REJECTED,
                'rejection_notes' => $reason,
            ]);

            return $mutation->refresh();
        });
    }

    /**
     * Execute all domain side-effects of an approved mutation atomically & idempotently.
     * Invoked both from `approveMutation()` and from `WorkflowService::approve()` inline inbox actions.
     */
    public function applyApprovedMutation(StudentMutation $mutation, User $approver): StudentMutation
    {
        return DB::transaction(function () use ($mutation, $approver) {
            $locked = StudentMutation::query()->lockForUpdate()->findOrFail($mutation->id);
            if ($locked->executed_at !== null) {
                return $locked;
            }

            if ($locked->type === StudentMutation::TYPE_OUT) {
                $this->executeMutationOut($locked, $approver);
            } else {
                $this->executeMutationIn($locked, $approver);
            }

            $locked->update([
                'status' => StudentMutation::STATUS_APPROVED,
                'approved_by' => $approver->id,
                'approved_at' => $locked->approved_at ?? now(),
                'executed_at' => now(),
            ]);

            $this->notifyWakaKesiswaan($locked->fresh(['student.user', 'classroom']), 'approved');

            return $locked->refresh();
        });
    }

    /**
     * Staf TU / Bendahara reviews ongoing billings for an outward mutation (`tagihan berjalan ditinjau`),
     * optionally cancelling strictly future unpaid billings with zero payments.
     */
    public function reviewMutationBillings(
        StudentMutation $mutation,
        User $reviewer,
        ?string $notes = null,
        bool $cancelFutureUnpaid = false,
    ): StudentMutation {
        if (! RoleMiddleware::allowsUser($reviewer, ['staf_tu', 'bendahara', 'admin', 'superadmin'])) {
            abort(403, __('Hanya Staf TU atau Bendahara yang berwenang meninjau penyelesaian tagihan mutasi.'));
        }

        if ($mutation->type !== StudentMutation::TYPE_OUT || ! $mutation->student_id) {
            throw ValidationException::withMessages([
                'type' => __('Tinjauan tagihan berjalan hanya berlaku untuk mutasi keluar.'),
            ]);
        }

        return DB::transaction(function () use ($mutation, $reviewer, $notes, $cancelFutureUnpaid) {
            $student = $mutation->student()->with(['parent'])->firstOrFail();
            $mutationDate = CarbonImmutable::parse((string) $mutation->mutation_date)->toDateString();

            $cancelledIds = [];
            if ($cancelFutureUnpaid) {
                $futureBillings = Billing::query()
                    ->where('student_id', $student->id)
                    ->where('status', 'unpaid')
                    ->whereDate('due_date', '>', $mutationDate)
                    ->whereDoesntHave('payments')
                    ->get();

                foreach ($futureBillings as $futureBilling) {
                    $cancelledIds[] = $futureBilling->id;
                    $futureBilling->delete();
                }
            }

            [$status, $summary] = $this->evaluateStudentBillings($student, $mutationDate);
            $summary['reviewed_by'] = $reviewer->id;
            $summary['reviewed_at'] = now()->toIso8601String();
            $summary['notes'] = $notes;
            $summary['cancelled_future_billing_ids'] = $cancelledIds;

            $finalStatus = $status === StudentMutation::BILLING_CLEARED
                ? ($cancelledIds !== [] ? StudentMutation::BILLING_SETTLED : StudentMutation::BILLING_CLEARED)
                : StudentMutation::BILLING_NEEDS_SETTLEMENT;

            $mutation->update([
                'billing_review_status' => $finalStatus,
                'billing_review_summary' => $summary,
            ]);

            if ($mutation->status === StudentMutation::STATUS_APPROVED && $student->parent) {
                $this->deactivateParentIfEligible($student->parent);
            }

            return $mutation->refresh();
        });
    }

    private function executeMutationOut(StudentMutation $mutation, User $approver): void
    {
        $student = Student::with(['user', 'parent'])->findOrFail($mutation->student_id);
        $mutationDate = CarbonImmutable::parse((string) $mutation->mutation_date)->toDateString();
        $previousStatus = (string) $student->status;

        // 1. Close active enrollment with `mutasi_keluar`
        $this->enrollmentService->closeActiveEnrollment(
            $student,
            ClassEnrollment::STATUS_MUTATED_OUT,
            $mutationDate,
            $mutation->reason,
            $approver
        );

        // 2. Preserve canonical student status `mutated`
        $student->update(['status' => 'mutated']);

        // 3. Deactivate student account & revoke tokens
        if ($student->user !== null) {
            $student->user->forceFill([
                'account_status' => 'nonaktif',
                'deactivated_reason' => ClassEnrollment::STATUS_MUTATED_OUT,
            ])->save();
            $student->user->tokens()->delete();
        }

        // 4. Review ongoing billings
        [$billingStatus, $billingSummary] = $this->evaluateStudentBillings($student, $mutationDate);
        $mutation->update([
            'classroom_id' => $mutation->classroom_id ?? $student->classroom_id,
            'billing_review_status' => $billingStatus,
            'billing_review_summary' => $billingSummary,
        ]);

        if ($billingStatus === StudentMutation::BILLING_NEEDS_SETTLEMENT) {
            $this->notifyFinanceStaffForSettlement($mutation, $student, $billingSummary);
        }

        // 5. Deactivate parent account if no other active children AND no outstanding billings
        if ($student->parent !== null) {
            $this->deactivateParentIfEligible($student->parent);
        }

        // 6. Enqueue K1 Dapodik sync item
        DapodikSyncItem::firstOrCreate(
            [
                'entity_type' => DapodikSyncItem::ENTITY_STUDENT,
                'entity_id' => $student->id,
                'field_name' => 'status',
                'new_value' => 'mutasi_keluar ('.($mutation->target_school ?: '-').')',
            ],
            [
                'dapodik_column_label' => 'Mutasi Keluar Peserta Didik',
                'old_value' => $previousStatus,
                'status' => DapodikSyncItem::STATUS_PENDING,
                'notes' => 'Alasan: '.$mutation->reason,
            ]
        );
    }

    private function executeMutationIn(StudentMutation $mutation, User $approver): void
    {
        $mutationDate = CarbonImmutable::parse((string) $mutation->mutation_date)->toDateString();
        $student = $mutation->student_id ? Student::with(['user', 'parent'])->find($mutation->student_id) : null;

        $classroomId = $mutation->classroom_id ?? $student?->classroom_id;
        $classroom = $classroomId ? Classroom::findOrFail($classroomId) : null;

        if (! $student) {
            $data = is_array($mutation->candidate_data) ? $mutation->candidate_data : [];
            $provisionedStudent = $this->accountProvisioningService->ensureStudentUser(
                (string) ($data['name'] ?? 'Siswa Pindahan'),
                $data['email'] ?? null,
                $data['phone_number'] ?? null
            );

            $student = Student::create([
                'user_id' => $provisionedStudent['user']->id,
                'classroom_id' => $classroom?->id,
                'nis' => $data['nis'] ?? null,
                'nisn' => $data['nisn'] ?? null,
                'nik' => $data['nik'] ?? null,
                'gender' => $data['gender'] ?? 'L',
                'birth_place' => $data['birth_place'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'mother_name' => $data['mother_name'] ?? null,
                'address' => $data['address'] ?? null,
                'status' => 'active',
            ]);

            $this->accountProvisioningService->ensureParentForStudent(
                $student,
                $data['parent_name'] ?? null,
                $data['parent_phone'] ?? null,
                $data['parent_email'] ?? null
            );

            $mutation->update(['student_id' => $student->id]);
        } else {
            $student->update([
                'status' => 'active',
                'classroom_id' => $classroom?->id ?? $student->classroom_id,
            ]);

            if ($student->user !== null && $student->user->account_status === 'nonaktif') {
                $student->user->forceFill([
                    'account_status' => 'aktif',
                    'deactivated_reason' => null,
                ])->save();
            }

            if ($student->parent !== null && $student->parent->account_status === 'nonaktif') {
                $student->parent->forceFill([
                    'account_status' => 'aktif',
                    'deactivated_reason' => null,
                ])->save();
            }
        }

        if ($classroom) {
            $schoolYear = $mutation->school_year_id
                ? SchoolYear::find($mutation->school_year_id)
                : $this->resolveSchoolYear($classroom->id);

            if ($schoolYear) {
                $this->enrollmentService->enroll(
                    $student,
                    $classroom,
                    $schoolYear,
                    $mutationDate,
                    $approver
                );
                $mutation->update([
                    'classroom_id' => $classroom->id,
                    'school_year_id' => $schoolYear->id,
                ]);
            }
        }

        DapodikSyncItem::firstOrCreate(
            [
                'entity_type' => DapodikSyncItem::ENTITY_STUDENT,
                'entity_id' => $student->id,
                'field_name' => 'status',
                'new_value' => 'mutasi_masuk ('.($classroom?->name ?? 'aktif').')',
            ],
            [
                'dapodik_column_label' => 'Pendaftaran Mutasi Masuk Peserta Didik',
                'old_value' => $mutation->target_school ?: 'Sekolah Asal',
                'status' => DapodikSyncItem::STATUS_PENDING,
                'notes' => 'Tanggal masuk: '.$mutationDate,
            ]
        );
    }

    /**
     * Evaluate unpaid/partial billings of a student at mutation time.
     *
     * @return array{0: string, 1: array<string, mixed>}
     */
    public function evaluateStudentBillings(Student $student, string $mutationDate): array
    {
        $openBillings = Billing::query()
            ->where('student_id', $student->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->get(['id', 'status', 'amount', 'remaining_balance', 'due_date']);

        $unpaidCount = $openBillings->where('status', 'unpaid')->count();
        $partialCount = $openBillings->where('status', 'partial')->count();
        $futureUnpaidIds = $openBillings
            ->filter(fn (Billing $b) => $b->status === 'unpaid' && $b->due_date !== null && (string) $b->due_date > $mutationDate)
            ->pluck('id')
            ->values()
            ->all();

        $totalOutstanding = (float) $openBillings->sum(
            fn (Billing $b) => (float) ($b->remaining_balance ?? $b->amount)
        );

        $status = $totalOutstanding > 0
            ? StudentMutation::BILLING_NEEDS_SETTLEMENT
            : StudentMutation::BILLING_CLEARED;

        return [
            $status,
            [
                'unpaid_count' => $unpaidCount,
                'partial_count' => $partialCount,
                'future_unpaid_count' => count($futureUnpaidIds),
                'future_unpaid_ids' => $futureUnpaidIds,
                'total_outstanding_amount' => $totalOutstanding,
                'billing_ids' => $openBillings->pluck('id')->values()->all(),
            ],
        ];
    }

    private function deactivateParentIfEligible(User $parent): void
    {
        $hasActiveChildren = Student::query()
            ->where('parent_id', $parent->id)
            ->where('status', 'active')
            ->exists();

        if ($hasActiveChildren) {
            return;
        }

        $hasOpenFamilyBillings = Billing::query()
            ->whereIn('student_id', Student::query()->where('parent_id', $parent->id)->select('id'))
            ->whereIn('status', ['unpaid', 'partial'])
            ->exists();

        if ($hasOpenFamilyBillings) {
            return;
        }

        $parent->forceFill([
            'account_status' => 'nonaktif',
            'deactivated_reason' => 'semua_anak_keluar',
        ])->save();
        $parent->tokens()->delete();
    }

    private function resolveSchoolYear(?int $classroomId = null): ?SchoolYear
    {
        if ($classroomId) {
            $classroom = Classroom::find($classroomId);
            if ($classroom?->school_year_id) {
                return SchoolYear::find($classroom->school_year_id);
            }
            if ($classroom && filled($classroom->academic_year)) {
                return SchoolYear::firstOrCreate(
                    ['name' => (string) $classroom->academic_year],
                    ['status' => SchoolYear::STATUS_ACTIVE]
                );
            }
        }

        $active = SchoolYear::query()->where('status', SchoolYear::STATUS_ACTIVE)->first();
        if ($active) {
            return $active;
        }

        $currentSemester = AcademicYear::current();
        if ($currentSemester) {
            if ($currentSemester->school_year_id) {
                return SchoolYear::find($currentSemester->school_year_id);
            }

            return SchoolYear::firstOrCreate(
                ['name' => (string) $currentSemester->year],
                ['status' => SchoolYear::STATUS_ACTIVE]
            );
        }

        return SchoolYear::query()->latest('id')->first();
    }

    /**
     * @param  array<string, mixed>  $validatedData
     * @return array<string, mixed>
     */
    private function extractCandidateData(array $validatedData): array
    {
        $nested = isset($validatedData['candidate_data']) && is_array($validatedData['candidate_data'])
            ? $validatedData['candidate_data']
            : [];

        return [
            'name' => $nested['name'] ?? $validatedData['student_name'] ?? null,
            'nis' => $nested['nis'] ?? $validatedData['nis'] ?? null,
            'nisn' => $nested['nisn'] ?? $validatedData['nisn'] ?? null,
            'nik' => $nested['nik'] ?? $validatedData['nik'] ?? null,
            'gender' => $nested['gender'] ?? $validatedData['gender'] ?? 'L',
            'birth_place' => $nested['birth_place'] ?? $validatedData['birth_place'] ?? null,
            'birth_date' => $nested['birth_date'] ?? $validatedData['birth_date'] ?? null,
            'mother_name' => $nested['mother_name'] ?? $validatedData['mother_name'] ?? null,
            'address' => $nested['address'] ?? $validatedData['address'] ?? null,
            'email' => $nested['email'] ?? $validatedData['student_email'] ?? null,
            'phone_number' => $nested['phone_number'] ?? $validatedData['student_phone'] ?? null,
            'parent_name' => $nested['parent_name'] ?? $validatedData['parent_name'] ?? null,
            'parent_phone' => $nested['parent_phone'] ?? $validatedData['parent_phone'] ?? null,
            'parent_email' => $nested['parent_email'] ?? $validatedData['parent_email'] ?? null,
        ];
    }

    private function notifyWakaKesiswaan(StudentMutation $mutation, string $stage): void
    {
        $recipients = User::query()
            ->where('role', 'waka_kesiswaan')
            ->orWhereHas('positionAssignments', function ($q) {
                $q->activeOn()->whereHas('position', fn ($pq) => $pq->where('code', 'waka_kesiswaan'));
            })
            ->get();

        $typeLabel = $mutation->type === StudentMutation::TYPE_IN ? 'Mutasi Masuk' : 'Mutasi Keluar';
        $studentName = $mutation->student_display_name;
        $title = $stage === 'approved'
            ? "{$typeLabel} Disetujui: {$studentName}"
            : "Permohonan {$typeLabel} Baru: {$studentName}";
        $description = $stage === 'approved'
            ? "{$typeLabel} untuk {$studentName} telah disetujui Kepala Sekolah dan dieksekusi ke riwayat rombel."
            : "Staf TU mencatat permohonan {$typeLabel} untuk {$studentName} ({$mutation->reason}).";

        foreach ($recipients as $recipient) {
            event(new NewNotificationEvent($recipient, [
                'id' => $mutation->id,
                'type' => 'student_mutation_'.$stage,
                'title' => $title,
                'description' => $description,
                'created_at' => now()->toDateTimeString(),
            ]));
        }
    }

    /**
     * @param  array<string, mixed>  $billingSummary
     */
    private function notifyFinanceStaffForSettlement(StudentMutation $mutation, Student $student, array $billingSummary): void
    {
        $recipients = User::query()
            ->whereIn('role', ['bendahara', 'staf_tu'])
            ->get();

        $student->loadMissing('user');
        $amountFormatted = 'Rp '.number_format((float) ($billingSummary['total_outstanding_amount'] ?? 0), 0, ',', '.');
        $studentName = $student->user?->name ?? 'Siswa';

        foreach ($recipients as $recipient) {
            event(new NewNotificationEvent($recipient, [
                'id' => $mutation->id,
                'type' => 'mutation_billing_review',
                'title' => "Tinjau Tagihan Mutasi Keluar: {$studentName}",
                'description' => "Siswa {$studentName} mutasi keluar dengan sisa tagihan berjalan sebesar {$amountFormatted}. Mohon tinjau penyelesaiannya.",
                'created_at' => now()->toDateTimeString(),
            ]));
        }
    }
}
