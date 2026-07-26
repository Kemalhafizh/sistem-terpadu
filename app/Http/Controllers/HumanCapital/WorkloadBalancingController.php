<?php

namespace App\Http\Controllers\HumanCapital;

use App\Http\Controllers\Controller;
use App\Models\TeacherWorkload;
use App\Models\User;
use App\Services\HumanCapital\WorkloadBalancingService;
use Illuminate\Http\Request;

class WorkloadBalancingController extends Controller
{
    public function dashboard()
    {
        $workloads = TeacherWorkload::with('employee')->get();
        $teachers = User::where('role', 'guru')->get();

        return view('workload.dashboard', compact('workloads', 'teachers'));
    }

    public function storeWorkload(Request $request, WorkloadBalancingService $service)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'teaching_hours_per_week' => 'required|integer|min:1',
            'additional_duties' => 'nullable|array',
            'extracurricular_duties' => 'nullable|array',
        ]);

        $service->calculateWorkload(
            $validated['employee_id'],
            $validated['teaching_hours_per_week'],
            $validated['additional_duties'] ?? ['Wali Kelas X'],
            $validated['extracurricular_duties'] ?? ['Pembina Pramuka']
        );

        return back()->with('success', 'Beban Kerja Guru (Workload Balancing) berhasil dikalkulasi!');
    }

    public function capacityReport()
    {
        $workloads = TeacherWorkload::with('employee')->get();

        $stats = [
            'total' => $workloads->count(),
            'under' => $workloads->where('status', 'under')->count(),
            'balanced' => $workloads->where('status', 'balanced')->count(),
            'over' => $workloads->where('status', 'over')->count(),
        ];

        return view('workload.capacity-report', compact('workloads', 'stats'));
    }
}
