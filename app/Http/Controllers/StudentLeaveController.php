<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentLeaveRequest;
use App\Services\Academic\StudentLeaveService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StudentLeaveController extends Controller
{
    public function __construct(
        protected StudentLeaveService $leaveService,
    ) {}

    /**
     * Parent Portal: List children & their leave/sick requests.
     */
    public function parentIndex(Request $request)
    {
        $parent = $request->user();
        $children = Student::with(['user', 'classroom'])
            ->where('parent_id', $parent->id)
            ->orderBy('nis')
            ->get();

        $leaveRequests = StudentLeaveRequest::with(['student.user', 'classroom', 'decider'])
            ->where(function ($q) use ($parent, $children) {
                $q->where('submitted_by', $parent->id);
                if ($children->isNotEmpty()) {
                    $q->orWhereIn('student_id', $children->pluck('id'));
                }
            })
            ->latest('id')
            ->paginate(20);

        return view('parent-portal.leave-requests', [
            'children' => $children,
            'leaveRequests' => $leaveRequests,
            'medicalLetterAfterDays' => $this->leaveService->medicalLetterAfterDays(),
            'autoApproveSickDays' => $this->leaveService->autoApproveSickDays(),
        ]);
    }

    /**
     * Parent Portal: Submit a new leave/sick request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'type' => ['required', Rule::in([StudentLeaveRequest::TYPE_SAKIT, StudentLeaveRequest::TYPE_IZIN])],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $student = Student::findOrFail((int) $validated['student_id']);

        try {
            $leave = $this->leaveService->submit(
                $request->user(),
                $student,
                $validated,
                $request->file('attachment'),
            );
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        }

        $message = $leave->status === StudentLeaveRequest::STATUS_AUTO_APPROVED
            ? __('Pengajuan sakit 1 hari otomatis disetujui sistem.')
            : __('Pengajuan izin/sakit berhasil dikirim ke wali kelas.');

        return redirect()
            ->route('parent-portal.leave-requests.index')
            ->with('success', $message);
    }

    /**
     * Parent Portal: Cancel a pending request before decision.
     */
    public function cancel(Request $request, string $leaveRequest)
    {
        $leave = $this->resolveLeave($leaveRequest);

        try {
            $this->leaveService->cancelByParent($request->user(), $leave);
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        }

        return back()->with('success', __('Pengajuan izin berhasil dibatalkan.'));
    }

    /**
     * Homeroom / Waka Kesiswaan: List leave requests for supervised classrooms.
     */
    public function teacherIndex(Request $request)
    {
        $actor = $request->user();
        $accessibleClassroomIds = $this->leaveService->accessibleClassroomIds($actor);
        $statusFilter = (string) $request->input('status', 'pending');

        $query = StudentLeaveRequest::with(['student.user', 'classroom', 'submitter', 'decider'])
            ->when($accessibleClassroomIds !== null, function ($q) use ($accessibleClassroomIds) {
                if ($accessibleClassroomIds->isEmpty()) {
                    $q->whereRaw('1 = 0');
                } else {
                    $q->whereIn('classroom_id', $accessibleClassroomIds);
                }
            })
            ->when($statusFilter !== '' && $statusFilter !== 'all', function ($q) use ($statusFilter) {
                if ($statusFilter === 'approved') {
                    $q->whereIn('status', StudentLeaveRequest::APPROVED_STATUSES);
                } else {
                    $q->where('status', $statusFilter);
                }
            })
            ->latest('id');

        $leaveRequests = $query->paginate(20)->withQueryString();

        return view('teacher.homeroom-leave-requests', [
            'leaveRequests' => $leaveRequests,
            'statusFilter' => $statusFilter,
        ]);
    }

    /**
     * Homeroom / Waka Kesiswaan: Approve a pending request.
     */
    public function approve(Request $request, string $leaveRequest)
    {
        $leave = $this->resolveLeave($leaveRequest);
        $validated = $request->validate([
            'decision_notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->leaveService->approve($request->user(), $leave, $validated['decision_notes'] ?? null);
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        }

        return back()->with('success', __('Pengajuan izin disetujui dan presensi telah diperbarui.'));
    }

    /**
     * Homeroom / Waka Kesiswaan: Reject a pending request.
     */
    public function reject(Request $request, string $leaveRequest)
    {
        $leave = $this->resolveLeave($leaveRequest);
        $validated = $request->validate([
            'decision_notes' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->leaveService->reject($request->user(), $leave, $validated['decision_notes']);
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        }

        return back()->with('success', __('Pengajuan izin ditolak.'));
    }

    /**
     * Homeroom / Waka Kesiswaan: Revoke a previously approved / auto-approved leave.
     */
    public function revoke(Request $request, string $leaveRequest)
    {
        $leave = $this->resolveLeave($leaveRequest);
        $validated = $request->validate([
            'decision_notes' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->leaveService->revoke($request->user(), $leave, $validated['decision_notes']);
        } catch (AuthorizationException $e) {
            abort(403, $e->getMessage());
        }

        return back()->with('success', __('Persetujuan izin dicabut dan presensi disinkronkan ulang.'));
    }

    /**
     * Protected download/preview of private medical letter attachment (IDOR-checked).
     */
    public function downloadAttachment(Request $request, string $leaveRequest)
    {
        $leave = $this->resolveLeave($leaveRequest);

        if (! $this->leaveService->canViewAttachment($request->user(), $leave)) {
            abort(403, __('Anda tidak berhak melihat lampiran izin ini.'));
        }

        if (! $leave->attachment_path || ! Storage::disk('local')->exists($leave->attachment_path)) {
            abort(404, __('Lampiran tidak ditemukan.'));
        }

        return Storage::disk('local')->response($leave->attachment_path);
    }

    private function resolveLeave(string $param): StudentLeaveRequest
    {
        return is_numeric($param)
            ? StudentLeaveRequest::findOrFail((int) $param)
            : StudentLeaveRequest::where('uuid', $param)->firstOrFail();
    }
}
