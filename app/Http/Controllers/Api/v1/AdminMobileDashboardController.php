<?php

namespace App\Http\Controllers\Api\v1;

use App\Events\EmergencyBroadcastEvent;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Billing;
use App\Models\ExamAttempt;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentPointLog;
use App\Models\User;
use App\Models\WorkflowRequest;
use App\Services\ApprovalInboxService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminMobileDashboardController extends Controller
{
    public function __construct(private readonly WorkflowService $workflow) {}

    private function authorizeAdmin(Request $request): void
    {
        $user = $request->user();
        $allowed = ['admin', 'superadmin', 'kepsek', 'kepala_sekolah'];

        if (! $user || array_intersect($user->effectiveRoles(), $allowed) === []) {
            abort(403, 'Akses ditolak. Hanya administrator dan pimpinan sekolah yang berwenang.');
        }
    }

    /**
     * Dashboard KPI aggregation
     */
    public function dashboard(Request $request)
    {
        $this->authorizeAdmin($request);

        // Attendance statuses are H/S/I/A: counting "present"/"late" matched nothing,
        // so this read 0% every day. The rate is over today's recorded attendance,
        // and null until anything has been recorded today.
        $today = Attendance::whereDate('date', now()->toDateString())->toBase()
            ->selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 'H' THEN 1 ELSE 0 END) as present")->first();
        $recorded = (int) ($today->total ?? 0);

        return response()->json([
            'success' => true,
            'data' => [
                'total_students' => Student::where('status', 'active')->count(),
                // What is still owed: unpaid and partly paid bills, by remaining balance.
                'unpaid_billings' => (float) Billing::whereIn('status', ['unpaid', 'partial'])->sum('remaining_balance'),
                'attendance_rate' => $recorded > 0 ? round((int) $today->present / $recorded * 100, 2) : null,
                // Requests waiting on this account's step, not every open request.
                'pending_approvals' => $this->workflow->getPendingForUser($request->user())->count(),
            ],
        ]);
    }

    /**
     * Detailed KPI list for executive view.
     *
     * follow-up: this used to return only 3 fields with a comment
     * literally saying "Dummy or expanded KPI details" for the other three
     * the roadmap named (teacher attendance %, SPP payment ratio per
     * cohort, CBT server usage). Those three are now computed from real
     * data below — except teacher attendance, which has no backing data
     * anywhere in this schema (see note on that key) and is left null
     * rather than invented.
     */
    public function kpiDetail(Request $request)
    {
        $this->authorizeAdmin($request);

        // SPP payment ratio grouped by academic year ("angkatan" proxy — this
        // schema has no separate student entry-cohort field; a billing's
        // academic_year_id is the real grouping that actually exists).
        $sppRatioByYear = Billing::selectRaw("academic_year_id, COUNT(*) as total_count, SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count")
            ->whereNotNull('academic_year_id')
            ->groupBy('academic_year_id')
            ->with('academicYear:id,year')
            ->get()
            ->map(function ($row) {
                return [
                    'academic_year' => $row->academicYear?->year ?? 'Tidak diketahui',
                    'total_billings' => (int) $row->total_count,
                    'paid_billings' => (int) $row->paid_count,
                    'paid_ratio_percent' => $row->total_count > 0
                        ? round(($row->paid_count / $row->total_count) * 100, 2)
                        : 0.0,
                ];
            })
            ->sortByDesc('academic_year')
            ->values();

        // CBT server usage: attempts genuinely in progress right now (real
        // concurrent load) plus a same-day total (real daily usage volume).
        // Both come from ExamAttempt — the actual CBT exam system Android's
        // CbtExamRoomScreen talks to.
        $activeCbtSessions = ExamAttempt::where('status', 'in_progress')->count();
        $cbtSessionsToday = ExamAttempt::whereDate('created_at', now()->toDateString())->count();

        return response()->json([
            'success' => true,
            'data' => [
                // Cash actually received this calendar month. Previously queried
                // `payment_date` on `billings`, a column that only exists on
                // `payments` — silently 0 on SQLite, "Unknown column" (HTTP 500
                // for this whole endpoint) on MySQL — and never scoped by year.
                'monthly_revenue' => (float) Payment::whereYear('payment_date', now()->year)
                    ->whereMonth('payment_date', now()->month)
                    ->sum('amount_paid'),
                'active_teachers' => User::whereIn('role', ['guru', 'teacher'])->count(),
                'avg_student_points' => StudentPointLog::avg('points') ?? 0,
                'spp_payment_ratio_by_cohort' => $sppRatioByYear,
                'cbt_server_usage' => [
                    'active_sessions' => $activeCbtSessions,
                    'sessions_today' => $cbtSessionsToday,
                ],
                // No Attendance/attendance-like record for TEACHERS exists in
                // this schema (Attendance is student_id-scoped only; a
                // teacher's `recorded_by` on a student's attendance row isn't
                // a reliable presence signal — a teacher can record it
                // without personally taking that class, or not record any
                // class at all on a day they're actually present). Rather
                // than approximate this into a misleading percentage, it's
                // reported honestly as null.
                'teacher_attendance_rate' => null,
                'teacher_attendance_rate_note' => 'Tidak ada data presensi guru di skema saat ini — belum ada tabel/kolom yang mencatat kehadiran guru itu sendiri (Attendance hanya mencatat kehadiran siswa).',
            ],
        ]);
    }

    /**
     * broadcast an emergency alert to every connected mobile app
     * (students, teachers, parents) in real time over the `campus.emergency`
     * public channel.
     */
    public function broadcastEmergency(Request $request)
    {
        $this->authorizeAdmin($request);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'location' => 'nullable|string|max:255',
        ]);

        $payload = [
            'id' => (string) Str::uuid(),
            'title' => $validated['title'],
            'message' => $validated['message'],
            'location' => $validated['location'] ?? 'Kampus '.school_short_name(),
            'timestamp' => now()->toIso8601String(),
            'issued_by' => $request->user()->name,
        ];

        event(new EmergencyBroadcastEvent($payload));

        return response()->json([
            'success' => true,
            'message' => 'Siaran darurat berhasil dikirim ke seluruh pengguna aplikasi.',
            'data' => $payload,
        ]);
    }

    /**
     * Requests whose current step belongs to this account's role (or was
     * delegated to it) — the ones it can actually decide.
     */
    public function pendingApprovals(Request $request)
    {
        $this->authorizeAdmin($request);

        $requests = $this->workflow->getPendingForUser($request->user())
            ->load(['definition:id,name,code,steps', 'requester:id,name,role'])
            ->sortBy(fn (WorkflowRequest $wr) => [$wr->due_at?->timestamp ?? PHP_INT_MAX, $wr->created_at?->timestamp])
            ->values()
            ->map(function (WorkflowRequest $wr) {
                $totalSteps = is_array($wr->definition?->steps) ? count($wr->definition->steps) : null;

                return [
                    'id' => $wr->id,
                    'uuid' => $wr->uuid,
                    'type_name' => $wr->definition?->name ?? $wr->definition?->code ?? 'Tidak diketahui',
                    'requester_name' => $wr->requester?->name ?? 'Tidak diketahui',
                    'requester_role' => $wr->requester?->role,
                    'notes' => $wr->notes,
                    'current_step' => $wr->current_step,
                    'total_steps' => $totalSteps,
                    'due_at' => $wr->due_at?->toIso8601String(),
                    'is_overdue' => $wr->due_at !== null && $wr->due_at->isPast(),
                    'created_at' => $wr->created_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    /**
     * Unified cross-module approval inbox for authenticated staff, teachers, and leadership.
     */
    public function unifiedPendingApprovals(Request $request, ApprovalInboxService $inbox)
    {
        $user = $request->user();
        $moduleFilter = $request->query('module');

        $allItems = $inbox->forUser($user);
        $filtered = $moduleFilter && $moduleFilter !== 'all'
            ? $allItems->where('module_group', (string) $moduleFilter)->values()
            : $allItems;

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $inbox->summaryByGroup($allItems),
                'items' => $inbox->serializeForApi($filtered),
            ],
        ]);
    }

    /**
     * Decides the current step through the workflow engine, as the web does:
     * only the step's role (or its delegate) may decide, approval advances to
     * the next step, and the final decision updates the request's record.
     * This used to mark any request approved outright, skipping every step.
     */
    public function processApproval(Request $request, $id)
    {
        $this->authorizeAdmin($request);
        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string',
        ]);

        $workflowRequest = WorkflowRequest::findOrFail($id);

        if (! in_array($workflowRequest->status, ['pending', 'in_review'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan ini sudah diputuskan.',
            ], 400);
        }

        if ($request->action === 'approve') {
            $this->workflow->approve($workflowRequest, $request->user(), $request->notes);
        } else {
            $this->workflow->reject($workflowRequest, $request->user(), $request->notes);
        }

        $workflowRequest->refresh();
        $message = match ($workflowRequest->status) {
            'approved' => 'Pengajuan disetujui.',
            'rejected' => 'Pengajuan ditolak.',
            default => 'Disetujui. Pengajuan diteruskan ke langkah berikutnya.',
        };

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $workflowRequest,
        ]);
    }
}
