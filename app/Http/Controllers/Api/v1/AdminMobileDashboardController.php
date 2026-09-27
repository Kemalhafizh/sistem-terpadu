<?php

namespace App\Http\Controllers\Api\v1;

use App\Events\EmergencyBroadcastEvent;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Billing;
use App\Models\ExamAttempt;
use App\Models\Payment;
use App\Models\Student;
use App\Models\WorkflowRequest;
use Illuminate\Http\Request;

class AdminMobileDashboardController extends Controller
{
    private function authorizeAdmin(Request $request): void
    {
        $role = $request->user()?->role;
        if (!in_array($role, ['admin', 'superadmin', 'kepsek', 'kepala_sekolah'], true)) {
            abort(403, 'Akses ditolak. Hanya administrator dan pimpinan sekolah yang berwenang.');
        }
    }

    /**
     * Dashboard KPI aggregation
     */
    public function dashboard(Request $request)
    {
        $this->authorizeAdmin($request);
        $studentCount = Student::count();
        $unpaidBillings = Billing::where('status', 'unpaid')->sum('amount');
        
        // Attendance today rate approximation
        $totalStudents = $studentCount > 0 ? $studentCount : 1;
        $attendedToday = Attendance::whereDate('date', now()->toDateString())
            ->whereIn('status', ['present', 'late'])
            ->count();
        $attendanceRate = round(($attendedToday / $totalStudents) * 100, 2);

        $pendingApprovals = WorkflowRequest::where('status', 'pending')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_students' => $studentCount,
                'unpaid_billings' => (float)$unpaidBillings,
                'attendance_rate' => $attendanceRate,
                'pending_approvals' => $pendingApprovals,
            ]
        ]);
    }

    /**
     * Detailed KPI list for executive view.
     *
     * FASE 71.4 follow-up: this used to return only 3 fields with a comment
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
        // CbtExamRoomScreen talks to (separate from the UTBK Tryout system).
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
                'active_teachers' => \App\Models\User::whereIn('role', ['guru', 'teacher'])->count(),
                'avg_student_points' => \App\Models\StudentPointLog::avg('points') ?? 0,
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
            ]
        ]);
    }

    /**
     * FASE 71.4: broadcast an emergency alert to every connected mobile app
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
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'title' => $validated['title'],
            'message' => $validated['message'],
            'location' => $validated['location'] ?? 'Kampus SMA Islam Sultan Agung 1',
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
     * FASE 71.4 follow-up: list pending approval requests — previously only
     * the count was exposed (dashboard()'s pending_approvals), with no way
     * for the mobile app to fetch the actual items to act on.
     */
    public function pendingApprovals(Request $request)
    {
        $this->authorizeAdmin($request);

        $requests = WorkflowRequest::where('status', 'pending')
            ->with(['definition:id,name,code,steps', 'requester:id,name,role'])
            ->orderBy('due_at')
            ->orderBy('created_at')
            ->get()
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
     * State machine processing for Approvals
     */
    public function processApproval(Request $request, $id)
    {
        $this->authorizeAdmin($request);
        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string',
        ]);

        $workflowRequest = WorkflowRequest::findOrFail($id);

        if ($workflowRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya request dengan status pending yang dapat diproses.',
            ], 400);
        }

        $newStatus = $request->action === 'approve' ? 'approved' : 'rejected';
        
        $workflowRequest->update([
            'status' => $newStatus,
            // Assuming there is some notes field or audit trailing in the actual DB
        ]);

        // Normally, we'd log this into an audit trail table (e.g. WorkflowApproval)
        if (class_exists(\App\Models\WorkflowApproval::class)) {
            \App\Models\WorkflowApproval::create([
                'request_id' => $workflowRequest->id,
                'approver_id' => $request->user()->id,
                'role' => $request->user()->role,
                'decision' => $newStatus,
                'remarks' => $request->notes,
                'step_order' => $workflowRequest->current_step ?? 1,
                'decided_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Request berhasil di-$newStatus.",
            'data' => $workflowRequest,
        ]);
    }
}
