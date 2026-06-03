<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\SmartAlert;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SmartAlertController extends Controller
{
    /**
     * Display a listing of the smart alerts with active filtering.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', SmartAlert::class);

        $user = auth()->user();
        $query = SmartAlert::with(['alertable', 'acknowledgedBy', 'resolvedBy']);

        // Role-based scoping
        if ($user->role === 'guru') {
            $classroom = Classroom::where('teacher_id', $user->id)->first();
            if ($classroom) {
                $studentIds = $classroom->students()->pluck('id');
                $query->where(function ($q) use ($studentIds, $user) {
                    $q->where(function ($sq) use ($studentIds) {
                        $sq->where('alertable_type', Student::class)
                           ->whereIn('alertable_id', $studentIds);
                    })->orWhere(function ($uq) use ($user) {
                        $uq->where('alertable_type', User::class)
                           ->where('alertable_id', $user->id);
                    });
                });
            } else {
                $query->where('alertable_type', User::class)->where('alertable_id', $user->id);
            }
        }

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default show only unresolved/active & acknowledged alerts
            $query->whereIn('status', ['active', 'acknowledged']);
        }

        $alerts = $query->latest()->paginate(15)->withQueryString();

        // Calculate KPI Metrics for the widgets (based on role scoping)
        $kpiQuery = SmartAlert::query();
        if ($user->role === 'guru') {
            $classroom = Classroom::where('teacher_id', $user->id)->first();
            if ($classroom) {
                $studentIds = $classroom->students()->pluck('id');
                $kpiQuery->where(function ($q) use ($studentIds, $user) {
                    $q->where(function ($sq) use ($studentIds) {
                        $sq->where('alertable_type', Student::class)
                           ->whereIn('alertable_id', $studentIds);
                    })->orWhere(function ($uq) use ($user) {
                        $uq->where('alertable_type', User::class)
                           ->where('alertable_id', $user->id);
                    });
                });
            } else {
                $kpiQuery->where('alertable_type', User::class)->where('alertable_id', $user->id);
            }
        }

        $stats = [
            'total_active' => (clone $kpiQuery)->where('status', 'active')->count(),
            'critical' => (clone $kpiQuery)->where('status', 'active')->where('severity', 'critical')->count(),
            'warning' => (clone $kpiQuery)->where('status', 'active')->where('severity', 'warning')->count(),
            'acknowledged' => (clone $kpiQuery)->where('status', 'acknowledged')->count(),
            'resolved' => (clone $kpiQuery)->where('status', 'resolved')->count(),
        ];

        return view('smart-alerts.index', compact('alerts', 'stats'));
    }

    /**
     * Acknowledge the smart alert.
     */
    public function acknowledge(SmartAlert $alert)
    {
        Gate::authorize('acknowledge', $alert);

        $alert->update([
            'status' => 'acknowledged',
            'acknowledged_by' => auth()->id(),
            'acknowledged_at' => now(),
        ]);

        return redirect()->route('smart-alerts.index')
            ->with('success', 'Smart Alert berhasil ditandai sebagai Diketahui.');
    }

    /**
     * Resolve the smart alert with notes.
     */
    public function resolve(SmartAlert $alert, Request $request)
    {
        Gate::authorize('resolve', $alert);

        $request->validate([
            'resolution_notes' => 'required|string|min:5',
        ]);

        $alert->update([
            'status' => 'resolved',
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
            'resolution_notes' => $request->resolution_notes,
        ]);

        return redirect()->route('smart-alerts.index')
            ->with('success', 'Masalah berhasil diselesaikan dan dicatat.');
    }

    /**
     * Display a list of alerts specifically for a single student.
     */
    public function studentAlerts(Student $student)
    {
        Gate::authorize('viewAny', SmartAlert::class);

        $user = auth()->user();
        if ($user->role === 'guru') {
            $classroom = Classroom::where('teacher_id', $user->id)->first();
            if (!$classroom || $student->classroom_id !== $classroom->id) {
                abort(403, 'Anda hanya dapat melihat data siswa di kelas Anda.');
            }
        }

        $alerts = SmartAlert::where('alertable_type', Student::class)
            ->where('alertable_id', $student->id)
            ->latest()
            ->get();

        return view('smart-alerts.student', compact('student', 'alerts'));
    }
}
