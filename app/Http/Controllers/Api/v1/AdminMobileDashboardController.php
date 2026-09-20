<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Billing;
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
     * Detailed KPI list for executive view
     */
    public function kpiDetail(Request $request)
    {
        $this->authorizeAdmin($request);
        // Dummy or expanded KPI details
        return response()->json([
            'success' => true,
            'data' => [
                'monthly_revenue' => Billing::where('status', 'paid')
                    ->whereMonth('payment_date', now()->month)
                    ->sum('amount'),
                'active_teachers' => \App\Models\User::whereIn('role', ['guru', 'teacher'])->count(),
                'avg_student_points' => \App\Models\StudentPointLog::avg('points') ?? 0,
            ]
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
