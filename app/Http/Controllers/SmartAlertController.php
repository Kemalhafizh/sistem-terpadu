<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\SmartAlert;
use App\Models\Student;
use App\Models\User;
use App\Models\Setting;
use App\Services\SmartAlertService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SmartAlertController extends Controller
{
    protected $service;

    public function __construct(SmartAlertService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the smart alerts with active filtering.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', SmartAlert::class);

        $user = auth()->user();
        $query = SmartAlert::with(['alertable', 'acknowledgedBy', 'resolvedBy', 'escalatedTo']);

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

        // Fetch supervisors for escalation dropdown (admin, superadmin, kepala_sekolah, waka_kesiswaan, waka_kurikulum)
        $supervisors = User::whereIn('role', ['admin', 'superadmin', 'kepala_sekolah', 'waka_kesiswaan', 'waka_kurikulum'])
            ->where('id', '!=', auth()->id())
            ->get();

        return view('smart-alerts.index', compact('alerts', 'stats', 'supervisors'));
    }

    /**
     * Display dashboard analytics.
     */
    public function dashboard()
    {
        abort_if(!in_array(auth()->user()->role, ['admin', 'superadmin', 'kepala_sekolah']), 403);

        $kpis = [
            'total' => SmartAlert::count(),
            'active' => SmartAlert::where('status', 'active')->count(),
            'critical' => SmartAlert::where('severity', 'critical')->where('status', 'active')->count(),
            'warning' => SmartAlert::where('severity', 'warning')->where('status', 'active')->count(),
            'resolved' => SmartAlert::where('status', 'resolved')->count(),
            'escalated' => SmartAlert::whereNotNull('escalated_to_id')->where('status', 'active')->count(),
        ];

        // Group by severity
        $severityStats = SmartAlert::selectRaw('severity, count(*) as count')
            ->groupBy('severity')
            ->pluck('count', 'severity');

        // Group by type
        $typeStats = SmartAlert::selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        // 7-day trend
        $trendData = [];
        $trendLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $trendLabels[] = Carbon::now()->subDays($i)->format('d M');
            $trendData['created'][] = SmartAlert::whereDate('created_at', $date)->count();
            $trendData['resolved'][] = SmartAlert::whereDate('resolved_at', $date)->count();
        }

        return view('smart-alerts.dashboard', compact('kpis', 'severityStats', 'typeStats', 'trendLabels', 'trendData'));
    }

    /**
     * Display configuration rules.
     */
    public function rulesConfig()
    {
        abort_if(!in_array(auth()->user()->role, ['admin', 'superadmin', 'kepala_sekolah']), 403);

        $rules = [
            'smart_alert_at_risk_gpa_threshold' => Setting::get('smart_alert_at_risk_gpa_threshold', 70),
            'smart_alert_at_risk_attendance_threshold' => Setting::get('smart_alert_at_risk_attendance_threshold', 80),
            'smart_alert_at_risk_bk_points_threshold' => Setting::get('smart_alert_at_risk_bk_points_threshold', 50),
            'smart_alert_at_risk_bk_points_critical_threshold' => Setting::get('smart_alert_at_risk_bk_points_critical_threshold', 75),
            'smart_alert_journal_gap_days' => Setting::get('smart_alert_journal_gap_days', 5),
            'smart_alert_journal_gap_critical_days' => Setting::get('smart_alert_journal_gap_critical_days', 10),
            'smart_alert_medicine_expiry_days' => Setting::get('smart_alert_medicine_expiry_days', 30),
            'smart_alert_overdue_asset_days' => Setting::get('smart_alert_overdue_asset_days', 7),
        ];

        return view('smart-alerts.rules', compact('rules'));
    }

    /**
     * Update configuration rules.
     */
    public function updateRules(Request $request)
    {
        abort_if(!in_array(auth()->user()->role, ['admin', 'superadmin', 'kepala_sekolah']), 403);

        $validated = $request->validate([
            'smart_alert_at_risk_gpa_threshold' => 'required|numeric|min:0|max:100',
            'smart_alert_at_risk_attendance_threshold' => 'required|numeric|min:0|max:100',
            'smart_alert_at_risk_bk_points_threshold' => 'required|integer|min:0',
            'smart_alert_at_risk_bk_points_critical_threshold' => 'required|integer|min:0',
            'smart_alert_journal_gap_days' => 'required|integer|min:0',
            'smart_alert_journal_gap_critical_days' => 'required|integer|min:0',
            'smart_alert_medicine_expiry_days' => 'required|integer|min:0',
            'smart_alert_overdue_asset_days' => 'required|integer|min:0',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value, 'smart_alert');
        }

        return redirect()->route('smart-alerts.rules')->with('success', 'Aturan deteksi Smart Alert berhasil diperbarui.');
    }

    /**
     * Display create manual alert form.
     */
    public function create()
    {
        abort_if(!in_array(auth()->user()->role, ['admin', 'superadmin', 'kepala_sekolah', 'bk']), 403);

        $students = Student::with('classroom')->get();
        $teachers = User::whereIn('role', ['guru', 'bk', 'staf_tu', 'waka_kurikulum', 'waka_kesiswaan', 'waka_sarpras', 'waka_humas'])->get();

        return view('smart-alerts.create', compact('students', 'teachers'));
    }

    /**
     * Store manual alert.
     */
    public function manualCreate(Request $request)
    {
        abort_if(!in_array(auth()->user()->role, ['admin', 'superadmin', 'kepala_sekolah', 'bk']), 403);

        $validated = $request->validate([
            'target_type' => 'required|in:student,user',
            'target_id' => 'required',
            'severity' => 'required|in:warning,critical',
            'title' => 'required|string|max:150|min:5',
            'description' => 'required|string|min:10',
        ]);

        $alertableType = $validated['target_type'] === 'student' ? Student::class : User::class;
        $alertableId = $validated['target_id'];

        $alert = SmartAlert::create([
            'type' => 'manual',
            'severity' => $validated['severity'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'status' => 'active',
            'alertable_type' => $alertableType,
            'alertable_id' => $alertableId,
            'context_data' => [
                'created_by_name' => auth()->user()->name,
                'manual_entry' => true
            ]
        ]);

        // Trigger Reverb & WhatsApp if critical
        if ($alert->severity === 'critical') {
            // Find target users to notify (admin, kepsek, bk)
            $roles = ['admin', 'superadmin', 'kepala_sekolah', 'bk'];
            $users = User::whereIn('role', $roles)->get();

            foreach ($users as $user) {
                event(new \App\Events\NewNotificationEvent($user, [
                    'id' => $alert->id,
                    'type' => 'smart_alert',
                    'title' => '🚨 ' . $alert->title,
                    'description' => $alert->description,
                    'severity' => $alert->severity,
                    'created_at' => $alert->created_at->toDateTimeString(),
                ]));

                if ($user->phone_number) {
                    try {
                        $message = "⚠️ *MANUAL SMART ALERT CRITICAL (SISTA)* ⚠️\n\n" .
                                   "Judul: *{$alert->title}*\n" .
                                   "Pencipta: " . auth()->user()->name . "\n" .
                                   "Deskripsi: {$alert->description}\n" .
                                   "Waktu: " . now()->format('d-m-Y H:i') . "\n\n" .
                                   "Silakan ulas di portal SISTA.";
                        app(\App\Services\WhatsappService::class)->sendMessage($user->phone_number, $message, 'smart_alert_manual');
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to send WhatsApp manual alert: " . $e->getMessage());
                    }
                }
            }
        }

        return redirect()->route('smart-alerts.index')->with('success', 'Manual Smart Alert berhasil diterbitkan.');
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
     * Escalate smart alert to supervisor.
     */
    public function escalate(SmartAlert $alert, Request $request)
    {
        Gate::authorize('escalate', $alert);

        $request->validate([
            'supervisor_id' => 'required|exists:users,id',
            'escalation_notes' => 'required|string|min:5',
        ]);

        $supervisor = User::findOrFail($request->supervisor_id);

        $this->service->escalateAlert($alert, $supervisor, $request->escalation_notes);

        return redirect()->route('smart-alerts.index')->with('success', 'Smart Alert berhasil dieskalasikan ke ' . $supervisor->name);
    }

    /**
     * Display list of resolved/escalated alert history.
     */
    public function history(Request $request)
    {
        $query = SmartAlert::with(['alertable', 'acknowledgedBy', 'resolvedBy', 'escalatedTo'])
            ->whereIn('status', ['resolved', 'acknowledged']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $alerts = $query->latest()->paginate(15)->withQueryString();

        return view('smart-alerts.history', compact('alerts'));
    }

    /**
     * Export active alerts to PDF.
     */
    public function exportReport(Request $request)
    {
        abort_if(!in_array(auth()->user()->role, ['admin', 'superadmin', 'kepala_sekolah']), 403);

        $alerts = SmartAlert::with(['alertable', 'acknowledgedBy', 'resolvedBy', 'escalatedTo'])
            ->latest()
            ->get();

        $pdf = Pdf::loadView('smart-alerts.report', compact('alerts'))->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Smart_Alerts_' . now()->format('YmdHis') . '.pdf');
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
