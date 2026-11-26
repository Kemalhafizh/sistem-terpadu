<?php

namespace App\Services;

use App\Http\Middleware\RoleMiddleware;
use App\Models\AssetLoan;
use App\Models\BudgetPlan;
use App\Models\GraduationBatch;
use App\Models\GraduationDecision;
use App\Models\LeaveRequest;
use App\Models\LessonPlan;
use App\Models\Letter;
use App\Models\LetterDisposition;
use App\Models\PettyCashTransaction;
use App\Models\ProfileChangeRequest;
use App\Models\ResourceBooking;
use App\Models\StudentLeaveRequest;
use App\Models\StudentMutation;
use App\Models\Syllabus;
use App\Models\TeachingJournal;
use App\Models\User;
use App\Services\Academic\StudentLeaveService;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;
use Illuminate\Support\Collection;

class ApprovalInboxService
{
    public const MODULE_GROUPS = [
        'all' => 'Semua',
        'workflow' => 'Alur Kerja',
        'letters' => 'Surat & Disposisi',
        'academic' => 'Akademik',
        'student_affairs' => 'Kesiswaan',
        'sarpras' => 'Sarpras',
        'finance' => 'Keuangan',
        'profile' => 'Data Diri',
    ];

    public function __construct(
        private readonly WorkflowService $workflowService,
        private readonly StudentLeaveService $studentLeaveService,
    ) {}

    public function forUser(User $user, ?string $moduleGroup = null): Collection
    {
        $items = collect();

        $this->appendWorkflowItems($items, $user);
        $this->appendDispositionItems($items, $user);
        $this->appendStudentLeaveItems($items, $user);
        $this->appendProfileChangeItems($items, $user);
        $this->appendAcademicItems($items, $user);
        $this->appendSarprasItems($items, $user);
        $this->appendFinanceItems($items, $user);

        $sorted = $items
            ->sortByDesc(fn (array $item) => [
                $item['urgency'] === 'high' ? 1 : 0,
                $item['submitted_at']?->timestamp ?? 0,
            ])
            ->values();

        if ($moduleGroup !== null && $moduleGroup !== '' && $moduleGroup !== 'all') {
            return $sorted->where('module_group', $moduleGroup)->values();
        }

        return $sorted;
    }

    /** @return array<string, int> */
    public function summaryByGroup(Collection $items): array
    {
        $summary = ['all' => $items->count()];
        foreach (array_keys(self::MODULE_GROUPS) as $key) {
            if ($key === 'all') {
                continue;
            }
            $summary[$key] = $items->where('module_group', $key)->count();
        }

        return $summary;
    }

    /** @return list<array<string, mixed>> */
    public function serializeForApi(Collection $items): array
    {
        return $items->map(fn (array $item) => [
            'module' => $item['module'],
            'module_group' => $item['module_group'],
            'module_label' => $item['module_label'],
            'title' => $item['title'],
            'subtitle' => $item['subtitle'],
            'requester_name' => $item['requester_name'],
            'submitted_at' => $item['submitted_at']?->toIso8601String(),
            'due_at' => $item['due_at']?->toIso8601String(),
            'is_overdue' => $item['is_overdue'],
            'urgency' => $item['urgency'],
            'url' => $item['url'],
            'model_type' => $item['model_type'],
            'model_id' => $item['model_id'],
            'can_decide_inline' => $item['can_decide_inline'],
        ])->values()->all();
    }

    private function appendWorkflowItems(Collection $items, User $user): void
    {
        $requests = $this->workflowService->getPendingForUser($user);
        if ($requests->isEmpty()) {
            return;
        }

        $requests->loadMissing(['definition', 'requester', 'requestable', 'approvals']);

        foreach ($requests as $wr) {
            if ((int) $wr->requester_id === (int) $user->id) {
                continue;
            }

            $requestable = $wr->requestable;
            if ($requestable instanceof Letter) {
                $requestable->loadMissing(['template', 'student.user', 'employee.user']);
            } elseif ($requestable instanceof GraduationBatch) {
                $requestable->loadMissing(['schoolYear', 'decisions']);
            } elseif ($requestable instanceof StudentMutation) {
                $requestable->loadMissing(['student.user', 'classroom']);
            }

            $subtitle = $wr->notes ?: '';
            $isHighPriority = false;
            if ($requestable instanceof Letter) {
                $recipient = $requestable->student?->user?->name
                    ?? $requestable->employee?->user?->name
                    ?? '-';
                $subtitle = ($requestable->template?->name ?? 'Surat Resmi').' • Penerima: '.$recipient;
            } elseif ($requestable instanceof LeaveRequest) {
                $subtitle = ($requestable->type_label ?? 'Cuti').' — '.($requestable->reason ?? '');
            } elseif ($requestable instanceof GraduationBatch) {
                $lulusCount = $requestable->decisions->where('decision', GraduationDecision::DECISION_GRADUATED)->count();
                $tidakLulusCount = $requestable->decisions->where('decision', GraduationDecision::DECISION_NOT_GRADUATED)->count();
                $taLabel = $requestable->schoolYear?->name ?? (string) $requestable->graduation_year;
                $skPart = $requestable->sk_number ? ' • SK: '.$requestable->sk_number : '';
                $subtitle = "TA {$taLabel} • {$lulusCount} lulus, {$tidakLulusCount} belum lulus{$skPart}";
                $isHighPriority = true;
            } elseif ($requestable instanceof StudentMutation) {
                $typeLabel = $requestable->type === StudentMutation::TYPE_IN ? 'Mutasi Masuk' : 'Mutasi Keluar';
                $classPart = $requestable->classroom?->name ? ' ('.$requestable->classroom->name.')' : '';
                $schoolPart = $requestable->target_school ? ' • '.$requestable->target_school : '';
                $subtitle = "{$typeLabel} — {$requestable->student_display_name}{$classPart}{$schoolPart}";
            }

            $isOverdue = $wr->due_at !== null && $wr->due_at->isPast();

            $items->push([
                'module' => 'workflow',
                'module_group' => 'workflow',
                'module_label' => $wr->definition?->name ?? 'Alur Persetujuan',
                'title' => $wr->definition?->name ?? 'Pengajuan Alur Kerja',
                'subtitle' => trim((string) $subtitle) ?: 'Menunggu keputusan langkah '.$wr->current_step,
                'requester_name' => $wr->requester?->name ?? '-',
                'submitted_at' => $wr->created_at,
                'due_at' => $wr->due_at,
                'is_overdue' => $isOverdue,
                'urgency' => ($isOverdue || $isHighPriority) ? 'high' : 'normal',
                'url' => route('workflows.show', $wr),
                'model_type' => 'WorkflowRequest',
                'model_id' => $wr->id,
                'can_decide_inline' => true,
            ]);
        }
    }

    private function appendDispositionItems(Collection $items, User $user): void
    {
        $dispositions = LetterDisposition::with(['incomingLetter', 'fromUser'])
            ->where('to_user_id', $user->id)
            ->whereIn('status', [LetterDisposition::STATUS_ASSIGNED, LetterDisposition::STATUS_IN_PROGRESS])
            ->get();

        foreach ($dispositions as $disposition) {
            if (! $disposition->incomingLetter) {
                continue;
            }

            $isOverdue = $disposition->due_at !== null && $disposition->due_at->isPast();
            $isHigh = $isOverdue || in_array($disposition->priority, ['segera', 'penting'], true);

            $items->push([
                'module' => 'disposition',
                'module_group' => 'letters',
                'module_label' => 'Disposisi Surat Masuk',
                'title' => $disposition->incomingLetter->agenda_number.' — '.$disposition->incomingLetter->subject,
                'subtitle' => 'Instruksi: '.$disposition->instruction,
                'requester_name' => $disposition->fromUser?->name ?? 'Pimpinan',
                'submitted_at' => $disposition->created_at,
                'due_at' => $disposition->due_at,
                'is_overdue' => $isOverdue,
                'urgency' => $isHigh ? 'high' : 'normal',
                'url' => route('incoming-letters.show', $disposition->incomingLetter),
                'model_type' => 'LetterDisposition',
                'model_id' => $disposition->id,
                'can_decide_inline' => false,
            ]);
        }
    }

    private function appendStudentLeaveItems(Collection $items, User $user): void
    {
        if (! RoleMiddleware::allowsUser($user, [...StudentDataAccess::TEACHER_ROLES, 'wali_kelas', 'waka_kesiswaan', 'admin'])) {
            return;
        }

        $accessibleClassroomIds = $this->studentLeaveService->accessibleClassroomIds($user);
        if ($accessibleClassroomIds !== null && $accessibleClassroomIds->isEmpty()) {
            return;
        }

        $leaves = StudentLeaveRequest::with(['student.user', 'classroom', 'submitter'])
            ->where('status', StudentLeaveRequest::STATUS_PENDING)
            ->where('submitted_by', '!=', $user->id)
            ->when($accessibleClassroomIds !== null, fn ($q) => $q->whereIn('classroom_id', $accessibleClassroomIds))
            ->latest('id')
            ->get();

        foreach ($leaves as $leave) {
            $items->push([
                'module' => 'leave_request',
                'module_group' => 'student_affairs',
                'module_label' => 'Izin / Sakit Siswa',
                'title' => strtoupper((string) $leave->type).' — '.($leave->student?->user?->name ?? '-'),
                'subtitle' => ($leave->classroom?->name ?? 'Tanpa Rombel').' • '.$leave->days_count.' hari',
                'requester_name' => $leave->submitter?->name ?? '-',
                'submitted_at' => $leave->created_at,
                'due_at' => $leave->start_date,
                'is_overdue' => false,
                'urgency' => $leave->escalated_at !== null ? 'high' : 'normal',
                'url' => route('teacher.leave-requests.index'),
                'model_type' => 'StudentLeaveRequest',
                'model_id' => $leave->id,
                'can_decide_inline' => false,
            ]);
        }
    }

    private function appendProfileChangeItems(Collection $items, User $user): void
    {
        if (! RoleMiddleware::allowsUser($user, ['superadmin', 'admin', 'staf_tu'])) {
            return;
        }

        $changes = ProfileChangeRequest::with(['requester', 'targetUser'])
            ->where('status', ProfileChangeRequest::STATUS_PENDING)
            ->where('requester_user_id', '!=', $user->id)
            ->latest('id')
            ->get();

        foreach ($changes as $change) {
            $items->push([
                'module' => 'profile_change',
                'module_group' => 'profile',
                'module_label' => 'Perubahan Data Diri',
                'title' => ($change->field_label ?: $change->field_name).' — '.($change->targetUser?->name ?? '-'),
                'subtitle' => 'Nilai baru: '.$change->new_value,
                'requester_name' => $change->requester?->name ?? '-',
                'submitted_at' => $change->created_at,
                'due_at' => null,
                'is_overdue' => false,
                'urgency' => $change->escalated_at !== null ? 'high' : 'normal',
                'url' => route('admin.profile-change-requests.index'),
                'model_type' => 'ProfileChangeRequest',
                'model_id' => $change->id,
                'can_decide_inline' => false,
            ]);
        }
    }

    private function appendAcademicItems(Collection $items, User $user): void
    {
        if ($user->can('reviewLessonPlan', LessonPlan::class)) {
            $rpps = LessonPlan::with(['user', 'subject', 'classroom'])
                ->where('status', 'submitted')
                ->where('user_id', '!=', $user->id)
                ->latest('id')
                ->get();

            foreach ($rpps as $rpp) {
                $items->push([
                    'module' => 'lesson_plan',
                    'module_group' => 'academic',
                    'module_label' => 'Review RPP / Modul Ajar',
                    'title' => $rpp->topic ?: 'Modul Ajar',
                    'subtitle' => ($rpp->subject?->name ?? '-').' • '.($rpp->classroom?->name ?? '-'),
                    'requester_name' => $rpp->user?->name ?? '-',
                    'submitted_at' => $rpp->created_at,
                    'due_at' => null,
                    'is_overdue' => false,
                    'urgency' => 'normal',
                    'url' => route('curriculum.lesson-plans.show', $rpp),
                    'model_type' => 'LessonPlan',
                    'model_id' => $rpp->id,
                    'can_decide_inline' => false,
                ]);
            }
        }

        if ($user->can('reviewSyllabus', Syllabus::class)) {
            $syllabi = Syllabus::with(['uploader', 'subject'])
                ->where('status', 'draft')
                ->where('uploaded_by', '!=', $user->id)
                ->latest('id')
                ->get();

            foreach ($syllabi as $syllabus) {
                $items->push([
                    'module' => 'syllabus',
                    'module_group' => 'academic',
                    'module_label' => 'Review Silabus / ATP',
                    'title' => ($syllabus->subject?->name ?? 'Silabus').' (Kelas '.$syllabus->grade_level.')',
                    'subtitle' => 'Semester '.ucfirst((string) $syllabus->semester),
                    'requester_name' => $syllabus->uploader?->name ?? '-',
                    'submitted_at' => $syllabus->created_at,
                    'due_at' => null,
                    'is_overdue' => false,
                    'urgency' => 'normal',
                    'url' => route('curriculum.syllabi.index'),
                    'model_type' => 'Syllabus',
                    'model_id' => $syllabus->id,
                    'can_decide_inline' => false,
                ]);
            }
        }

        if ($user->can('review', TeachingJournal::class)) {
            $journals = TeachingJournal::with(['teacher', 'subject', 'classroom'])
                ->where('status', 'submitted')
                ->where('user_id', '!=', $user->id)
                ->latest('id')
                ->get();

            foreach ($journals as $journal) {
                $items->push([
                    'module' => 'teaching_journal',
                    'module_group' => 'academic',
                    'module_label' => 'Verifikasi Jurnal Mengajar',
                    'title' => ($journal->subject?->name ?? 'Jurnal').' — '.($journal->topic ?? '-'),
                    'subtitle' => ($journal->classroom?->name ?? '-').' • '.$journal->teaching_date?->translatedFormat('d M Y'),
                    'requester_name' => $journal->teacher?->name ?? '-',
                    'submitted_at' => $journal->created_at,
                    'due_at' => null,
                    'is_overdue' => false,
                    'urgency' => 'normal',
                    'url' => route('teaching-journals.show', $journal),
                    'model_type' => 'TeachingJournal',
                    'model_id' => $journal->id,
                    'can_decide_inline' => false,
                ]);
            }
        }
    }

    private function appendSarprasItems(Collection $items, User $user): void
    {
        if ($user->can('approve', AssetLoan::class)) {
            $loans = AssetLoan::with(['asset', 'borrower'])
                ->where('status', 'pending')
                ->where('borrower_id', '!=', $user->id)
                ->latest('id')
                ->get();

            foreach ($loans as $loan) {
                $items->push([
                    'module' => 'asset_loan',
                    'module_group' => 'sarpras',
                    'module_label' => 'Peminjaman Aset',
                    'title' => $loan->asset?->name ?? 'Peminjaman Aset',
                    'subtitle' => $loan->purpose ?: 'Menunggu persetujuan Sarpras',
                    'requester_name' => $loan->borrower?->name ?? '-',
                    'submitted_at' => $loan->created_at,
                    'due_at' => $loan->expected_return_at,
                    'is_overdue' => false,
                    'urgency' => 'normal',
                    'url' => route('assets.loans.show', $loan),
                    'model_type' => 'AssetLoan',
                    'model_id' => $loan->id,
                    'can_decide_inline' => false,
                ]);
            }
        }

        if (RoleMiddleware::allowsUser($user, ['admin', 'superadmin', 'waka_sarpras', 'staf_tu'])) {
            $bookings = ResourceBooking::with(['resource', 'user'])
                ->where('status', 'pending')
                ->where('booked_by', '!=', $user->id)
                ->latest('id')
                ->get();

            foreach ($bookings as $booking) {
                $items->push([
                    'module' => 'resource_booking',
                    'module_group' => 'sarpras',
                    'module_label' => 'Reservasi Ruangan / Fasilitas',
                    'title' => ($booking->resource?->name ?? 'Fasilitas').' — '.$booking->purpose,
                    'subtitle' => $booking->start_datetime?->translatedFormat('d M Y H:i'),
                    'requester_name' => $booking->user?->name ?? '-',
                    'submitted_at' => $booking->created_at,
                    'due_at' => $booking->start_datetime,
                    'is_overdue' => false,
                    'urgency' => 'normal',
                    'url' => route('resource_bookings.index'),
                    'model_type' => 'ResourceBooking',
                    'model_id' => $booking->id,
                    'can_decide_inline' => false,
                ]);
            }
        }
    }

    private function appendFinanceItems(Collection $items, User $user): void
    {
        if (RoleMiddleware::allowsUser($user, ['staf_tu', ...Roles::PRINCIPAL, 'admin'])) {
            $pettyCashes = PettyCashTransaction::with(['fund', 'requester'])
                ->where('approval_status', 'pending')
                ->where('requested_by', '!=', $user->id)
                ->latest('id')
                ->get();

            foreach ($pettyCashes as $tx) {
                $items->push([
                    'module' => 'petty_cash',
                    'module_group' => 'finance',
                    'module_label' => 'Persetujuan Kas Kecil',
                    'title' => ($tx->fund?->fund_name ?? 'Kas Kecil').' — '.$tx->description,
                    'subtitle' => 'Rp '.number_format((float) $tx->amount, 0, ',', '.'),
                    'requester_name' => $tx->requester?->name ?? '-',
                    'submitted_at' => $tx->created_at,
                    'due_at' => null,
                    'is_overdue' => false,
                    'urgency' => 'normal',
                    'url' => route('treasury.petty-cash.approvals'),
                    'model_type' => 'PettyCashTransaction',
                    'model_id' => $tx->id,
                    'can_decide_inline' => false,
                ]);
            }
        }

        if (RoleMiddleware::allowsUser($user, [...BudgetPlan::APPROVER_ROLES, 'admin'])) {
            $budgets = BudgetPlan::with('creator')
                ->where('status', 'submitted')
                ->where('created_by', '!=', $user->id)
                ->latest('id')
                ->get();

            foreach ($budgets as $plan) {
                $items->push([
                    'module' => 'budget_plan',
                    'module_group' => 'finance',
                    'module_label' => 'Persetujuan RKAS / RAPBS',
                    'title' => $plan->title.' (TA '.$plan->fiscal_year.')',
                    'subtitle' => 'Rencana belanja: Rp '.number_format((float) $plan->total_expense_planned, 0, ',', '.'),
                    'requester_name' => $plan->creator?->name ?? '-',
                    'submitted_at' => $plan->created_at,
                    'due_at' => null,
                    'is_overdue' => false,
                    'urgency' => 'high',
                    'url' => route('financial.budget.show', $plan->id),
                    'model_type' => 'BudgetPlan',
                    'model_id' => $plan->id,
                    'can_decide_inline' => false,
                ]);
            }
        }
    }
}
