<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentLeaveRequest;
use App\Services\Academic\StudentLeaveService;
use App\Traits\ApiResponseTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StudentLeaveApiController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected StudentLeaveService $leaveService,
    ) {}

    /**
     * Parent: List leave/sick requests for own children.
     */
    public function parentIndex(Request $request): JsonResponse
    {
        $parent = $request->user();
        $childIds = Student::where('parent_id', $parent->id)->pluck('id');

        $leaves = StudentLeaveRequest::with(['student.user', 'classroom', 'decider'])
            ->where(function ($q) use ($parent, $childIds) {
                $q->where('submitted_by', $parent->id);
                if ($childIds->isNotEmpty()) {
                    $q->orWhereIn('student_id', $childIds);
                }
            })
            ->latest('id')
            ->limit(100)
            ->get()
            ->map(fn (StudentLeaveRequest $leave) => $this->formatLeave($leave))
            ->values();

        return $this->successResponse($leaves, __('Daftar pengajuan izin anak berhasil diambil.'));
    }

    /**
     * Parent: Submit leave/sick request for child.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => ['nullable', 'integer'],
            'student_uuid' => ['nullable', 'string'],
            'type' => ['required', Rule::in([StudentLeaveRequest::TYPE_SAKIT, StudentLeaveRequest::TYPE_IZIN])],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $student = $this->resolveStudent($validated['student_id'] ?? null, $validated['student_uuid'] ?? null);
        if (! $student) {
            return $this->errorResponse(__('Siswa tidak ditemukan.'), 404);
        }

        try {
            $leave = $this->leaveService->submit(
                $request->user(),
                $student,
                $validated,
                $request->file('attachment'),
            );
        } catch (AuthorizationException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }

        return $this->successResponse(
            $this->formatLeave($leave),
            $leave->status === StudentLeaveRequest::STATUS_AUTO_APPROVED
                ? __('Izin sakit 1 hari otomatis disetujui.')
                : __('Pengajuan izin berhasil dikirim.'),
            201,
        );
    }

    /**
     * Parent: Cancel a pending request.
     */
    public function cancel(Request $request, string $leaveRequest): JsonResponse
    {
        $leave = $this->resolveLeave($leaveRequest);

        try {
            $updated = $this->leaveService->cancelByParent($request->user(), $leave);
        } catch (AuthorizationException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }

        return $this->successResponse($this->formatLeave($updated), __('Pengajuan izin dibatalkan.'));
    }

    /**
     * Teacher/Homeroom/Kesiswaan: List leave requests in accessible classrooms.
     */
    public function teacherIndex(Request $request): JsonResponse
    {
        $actor = $request->user();
        $accessibleClassroomIds = $this->leaveService->accessibleClassroomIds($actor);
        $statusFilter = (string) $request->query('status', '');

        $leaves = StudentLeaveRequest::with(['student.user', 'classroom', 'submitter', 'decider'])
            ->when($accessibleClassroomIds !== null, function ($q) use ($accessibleClassroomIds) {
                if ($accessibleClassroomIds->isEmpty()) {
                    $q->whereRaw('1 = 0');
                } else {
                    $q->whereIn('classroom_id', $accessibleClassroomIds);
                }
            })
            ->when($statusFilter !== '', fn ($q) => $q->where('status', $statusFilter))
            ->latest('id')
            ->limit(100)
            ->get()
            ->map(fn (StudentLeaveRequest $leave) => $this->formatLeave($leave))
            ->values();

        return $this->successResponse($leaves, __('Daftar pengajuan izin siswa berhasil diambil.'));
    }

    public function approve(Request $request, string $leaveRequest): JsonResponse
    {
        $leave = $this->resolveLeave($leaveRequest);
        $validated = $request->validate([
            'decision_notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $updated = $this->leaveService->approve($request->user(), $leave, $validated['decision_notes'] ?? null);
        } catch (AuthorizationException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }

        return $this->successResponse($this->formatLeave($updated), __('Pengajuan izin disetujui.'));
    }

    public function reject(Request $request, string $leaveRequest): JsonResponse
    {
        $leave = $this->resolveLeave($leaveRequest);
        $validated = $request->validate([
            'decision_notes' => ['required', 'string', 'max:500'],
        ]);

        try {
            $updated = $this->leaveService->reject($request->user(), $leave, $validated['decision_notes']);
        } catch (AuthorizationException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }

        return $this->successResponse($this->formatLeave($updated), __('Pengajuan izin ditolak.'));
    }

    public function revoke(Request $request, string $leaveRequest): JsonResponse
    {
        $leave = $this->resolveLeave($leaveRequest);
        $validated = $request->validate([
            'decision_notes' => ['required', 'string', 'max:500'],
        ]);

        try {
            $updated = $this->leaveService->revoke($request->user(), $leave, $validated['decision_notes']);
        } catch (AuthorizationException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }

        return $this->successResponse($this->formatLeave($updated), __('Persetujuan izin dicabut.'));
    }

    public function downloadAttachment(Request $request, string $leaveRequest)
    {
        $leave = $this->resolveLeave($leaveRequest);

        if (! $this->leaveService->canViewAttachment($request->user(), $leave)) {
            return $this->errorResponse(__('Anda tidak berhak mengakses lampiran izin ini.'), 403);
        }

        if (! $leave->attachment_path || ! Storage::disk('local')->exists($leave->attachment_path)) {
            return $this->errorResponse(__('Lampiran tidak ditemukan.'), 404);
        }

        return Storage::disk('local')->response($leave->attachment_path);
    }

    private function resolveStudent(mixed $studentId, ?string $studentUuid): ?Student
    {
        if ($studentId !== null) {
            return Student::find((int) $studentId);
        }
        if (filled($studentUuid)) {
            return Student::where('uuid', $studentUuid)->first();
        }

        return null;
    }

    private function resolveLeave(string $param): StudentLeaveRequest
    {
        return is_numeric($param)
            ? StudentLeaveRequest::findOrFail((int) $param)
            : StudentLeaveRequest::where('uuid', $param)->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatLeave(StudentLeaveRequest $leave): array
    {
        $leave->loadMissing(['student.user', 'classroom', 'decider']);

        return [
            'id' => $leave->id,
            'uuid' => $leave->uuid,
            'student_id' => $leave->student_id,
            'student_uuid' => $leave->student?->uuid,
            'student_name' => $leave->student?->user?->name,
            'student_nis' => $leave->student?->nis,
            'classroom_id' => $leave->classroom_id,
            'classroom_name' => $leave->classroom?->name,
            'type' => $leave->type,
            'start_date' => $leave->start_date?->toDateString(),
            'end_date' => $leave->end_date?->toDateString(),
            'days_count' => $leave->days_count,
            'reason' => $leave->reason,
            'has_attachment' => (bool) $leave->attachment_path,
            'attachment_url' => $leave->attachment_path
                ? url("/api/v1/student-leave-requests/{$leave->uuid}/attachment")
                : null,
            'status' => $leave->status,
            'auto_approved' => (bool) $leave->auto_approved,
            'decided_by' => $leave->decided_by,
            'decided_by_name' => $leave->decider?->name,
            'decided_at' => $leave->decided_at?->toIso8601String(),
            'decision_notes' => $leave->decision_notes,
            'escalated_at' => $leave->escalated_at?->toIso8601String(),
            'created_at' => $leave->created_at?->toIso8601String(),
        ];
    }
}
