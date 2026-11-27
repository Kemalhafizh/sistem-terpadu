<?php

namespace App\Http\Controllers;

use App\Models\GraduationBatch;
use App\Models\Letter;
use App\Models\StudentMutation;
use App\Models\User;
use App\Models\WorkflowApproval;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowRequest;
use App\Services\ApprovalInboxService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class WorkflowController extends Controller
{
    protected $service;

    public function __construct(WorkflowService $service)
    {
        $this->service = $service;
    }

    /**
     * Dashboard: pending counts per tipe, SLA breached, avg approval time.
     */
    public function dashboard()
    {
        $user = auth()->user();

        // 1. KPI Cards
        $kpi = [
            'pending' => WorkflowRequest::whereIn('status', ['pending', 'in_review'])->count(),
            'approved' => WorkflowRequest::where('status', 'approved')->count(),
            'rejected' => WorkflowRequest::where('status', 'rejected')->count(),
            'revision' => WorkflowRequest::where('status', 'revision')->count(),
        ];

        // My pending tasks count
        $myPendingCount = $this->service->getPendingForUser($user)->count();

        // Overdue (SLA breached)
        $overdueCount = WorkflowRequest::whereIn('status', ['pending', 'in_review'])
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->count();

        // 2. Average Resolution Time (in hours)
        $resolvedRequests = WorkflowRequest::whereIn('status', ['approved', 'rejected'])->get();
        $totalHours = 0;
        $resolvedCount = $resolvedRequests->count();
        foreach ($resolvedRequests as $req) {
            $totalHours += $req->created_at->diffInHours($req->updated_at);
        }
        $avgResolutionTime = $resolvedCount > 0 ? round($totalHours / $resolvedCount, 1) : 0;

        // 3. Bottlenecks (Roles with most active pending approvals on open requests)
        $bottlenecks = WorkflowApproval::query()
            ->join('workflow_requests', function ($join) {
                $join->on('workflow_approvals.request_id', '=', 'workflow_requests.id')
                    ->whereColumn('workflow_approvals.step_order', 'workflow_requests.current_step');
            })
            ->whereIn('workflow_requests.status', ['pending', 'in_review'])
            ->where('workflow_approvals.decision', 'pending')
            ->select('workflow_approvals.role as role', DB::raw('count(*) as count'))
            ->groupBy('workflow_approvals.role')
            ->orderByDesc('count')
            ->get();

        // 4. SLA Breached List (Overdue Requests)
        $overdueRequests = WorkflowRequest::whereIn('status', ['pending', 'in_review'])
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->with(['definition', 'requester'])
            ->latest()
            ->take(5)
            ->get();

        return view('workflows.dashboard', compact('kpi', 'myPendingCount', 'overdueCount', 'avgResolutionTime', 'bottlenecks', 'overdueRequests'));
    }

    /**
     * Kelola definisi workflow (CRUD).
     */
    public function definitions()
    {
        $definitions = WorkflowDefinition::withCount('requests')->get();
        $roles = ['admin', 'superadmin', 'kepala_sekolah', 'waka_kurikulum', 'waka_kesiswaan', 'waka_sarpras', 'waka_humas', 'staf_tu', 'bk', 'guru'];

        return view('workflows.definitions', compact('definitions', 'roles'));
    }

    /**
     * Buat workflow baru.
     */
    public function storeDefinition(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:workflow_definitions,code',
            'description' => 'nullable|string',
            'steps' => 'required|array|min:1',
            'steps.*.role' => 'required|string',
            'steps.*.order' => 'required|integer|min:1',
            'steps.*.sla_hours' => 'nullable|integer|min:1',
        ]);

        // Format steps to match expected JSON structure
        $steps = [];
        foreach ($validated['steps'] as $step) {
            $steps[] = [
                'order' => (int) $step['order'],
                'role' => $step['role'],
                'sla_hours' => isset($step['sla_hours']) ? (int) $step['sla_hours'] : 48,
            ];
        }

        // Sort steps by order ascending
        usort($steps, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        WorkflowDefinition::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'],
            'steps' => $steps,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('workflows.definitions')
            ->with('success', 'Definisi workflow berhasil dibuat.');
    }

    /**
     * Edit workflow.
     */
    public function updateDefinition($id, Request $request)
    {
        $definition = WorkflowDefinition::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'steps' => 'required|array|min:1',
            'steps.*.role' => 'required|string',
            'steps.*.order' => 'required|integer|min:1',
            'steps.*.sla_hours' => 'nullable|integer|min:1',
        ]);

        $steps = [];
        foreach ($validated['steps'] as $step) {
            $steps[] = [
                'order' => (int) $step['order'],
                'role' => $step['role'],
                'sla_hours' => isset($step['sla_hours']) ? (int) $step['sla_hours'] : 48,
            ];
        }

        usort($steps, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        $definition->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'steps' => $steps,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('workflows.definitions')
            ->with('success', 'Definisi workflow berhasil diperbarui.');
    }

    /**
     * Hapus workflow.
     */
    public function deleteDefinition($id)
    {
        $definition = WorkflowDefinition::findOrFail($id);

        if ($definition->requests()->count() > 0) {
            return redirect()->route('workflows.definitions')
                ->with('error', 'Definisi workflow tidak dapat dihapus karena sudah memiliki data pengajuan.');
        }

        $definition->delete();

        return redirect()->route('workflows.definitions')
            ->with('success', 'Definisi workflow berhasil dihapus.');
    }

    /**
     * Tampilkan daftar pengajuan yang sedang menunggu persetujuan pengguna saat ini.
     */
    public function pending(Request $request, ApprovalInboxService $inbox)
    {
        $user = auth()->user();
        $requests = $this->service->getPendingForUser($user);
        $requests->loadMissing(['definition', 'requester', 'requestable', 'approvals']);

        $allUnifiedItems = $inbox->forUser($user);
        $moduleSummary = $inbox->summaryByGroup($allUnifiedItems);
        $activeModule = (string) $request->input('module', 'all');
        $unifiedItems = $activeModule !== '' && $activeModule !== 'all'
            ? $allUnifiedItems->where('module_group', $activeModule)->values()
            : $allUnifiedItems;

        return view('workflows.pending', compact('requests', 'unifiedItems', 'moduleSummary', 'activeModule'));
    }

    /**
     * Tampilkan daftar pengajuan yang diajukan oleh pengguna saat ini.
     */
    public function myRequests()
    {
        $requests = WorkflowRequest::where('requester_id', auth()->id())
            ->with(['definition', 'approvals.approver', 'approvals.delegatedTo'])
            ->latest()
            ->get();

        return view('workflows.my-requests', compact('requests'));
    }

    /**
     * Tampilkan rincian detail pengajuan tertentu polimorfik.
     */
    public function show(WorkflowRequest $workflowRequest)
    {
        Gate::authorize('view', $workflowRequest);

        $workflowRequest->load(['definition', 'requester', 'approvals.approver', 'approvals.delegatedTo', 'requestable']);

        $requestable = $workflowRequest->requestable;
        if ($requestable instanceof GraduationBatch) {
            $requestable->loadMissing(['schoolYear', 'meetingNote', 'decisions']);
        } elseif ($requestable instanceof StudentMutation) {
            $requestable->loadMissing(['student.user', 'classroom', 'schoolYear']);
        } elseif ($requestable instanceof Letter) {
            $requestable->loadMissing(['template', 'student.user', 'employee.user']);
        }

        // Dapatkan semua user staff (termasuk pemegang jabatan aktif) untuk opsi delegasi
        $staffUsers = User::where(function ($q) {
            $q->whereIn('role', ['admin', 'superadmin', 'kepala_sekolah', 'kepsek', 'waka_kurikulum', 'waka_kesiswaan', 'waka_sarpras', 'waka_humas', 'staf_tu', 'bk', 'guru', 'teacher', 'wali_kelas'])
                ->orWhereHas('positionAssignments', fn ($pq) => $pq->activeOn());
        })
            ->where('id', '!=', auth()->id())
            ->orderBy('name')
            ->get();

        return view('workflows.show', [
            'request' => $workflowRequest,
            'staffUsers' => $staffUsers,
        ]);
    }

    /**
     * Aksi persetujuan untuk langkah saat ini.
     */
    public function approve(WorkflowRequest $workflowRequest, Request $request)
    {
        $currentApproval = $workflowRequest->currentApproval;
        if (! $currentApproval) {
            abort(400, 'Langkah persetujuan aktif tidak ditemukan.');
        }

        $isAuthorized = (in_array($currentApproval->role, auth()->user()->effectiveRoles(), true) && $currentApproval->delegated_to_id === null)
            || (int) $currentApproval->delegated_to_id === (int) auth()->id();

        if (! $isAuthorized) {
            abort(403, 'Anda tidak berwenang memberikan persetujuan pada langkah ini.');
        }

        $this->service->approve($workflowRequest, auth()->user(), $request->remarks);

        return redirect()->route('workflows.pending')
            ->with('success', 'Pengajuan berhasil disetujui.');
    }

    /**
     * Aksi penolakan untuk pengajuan saat ini.
     */
    public function reject(WorkflowRequest $workflowRequest, Request $request)
    {
        $currentApproval = $workflowRequest->currentApproval;
        if (! $currentApproval) {
            abort(400, 'Langkah persetujuan aktif tidak ditemukan.');
        }

        $isAuthorized = (in_array($currentApproval->role, auth()->user()->effectiveRoles(), true) && $currentApproval->delegated_to_id === null)
            || (int) $currentApproval->delegated_to_id === (int) auth()->id();

        if (! $isAuthorized) {
            abort(403, 'Anda tidak berwenang memberikan tindakan pada langkah ini.');
        }

        $request->validate([
            'remarks' => 'required|string|min:5',
        ]);

        $this->service->reject($workflowRequest, auth()->user(), $request->remarks);

        return redirect()->route('workflows.pending')
            ->with('success', 'Pengajuan telah ditolak dan dihentikan.');
    }

    /**
     * Aksi revisi (kembalikan untuk diperbaiki).
     */
    public function revise(WorkflowRequest $workflowRequest, Request $request)
    {
        $currentApproval = $workflowRequest->currentApproval;
        if (! $currentApproval) {
            abort(400, 'Langkah persetujuan aktif tidak ditemukan.');
        }

        $isAuthorized = (in_array($currentApproval->role, auth()->user()->effectiveRoles(), true) && $currentApproval->delegated_to_id === null)
            || (int) $currentApproval->delegated_to_id === (int) auth()->id();

        if (! $isAuthorized) {
            abort(403, 'Anda tidak berwenang meminta revisi.');
        }

        $request->validate([
            'remarks' => 'required|string|min:5',
        ]);

        $this->service->revise($workflowRequest, auth()->user(), $request->remarks);

        return redirect()->route('workflows.pending')
            ->with('success', 'Pengajuan dikembalikan ke pengaju untuk direvisi.');
    }

    /**
     * Aksi pengiriman ulang setelah revisi.
     */
    public function resubmit(WorkflowRequest $workflowRequest, Request $request)
    {
        if ((int) $workflowRequest->requester_id !== (int) auth()->id()) {
            abort(403, 'Hanya pengaju yang dapat melakukan resubmit.');
        }

        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $this->service->resubmit($workflowRequest, auth()->user(), $request->notes);

        return redirect()->route('workflows.my-requests')
            ->with('success', 'Pengajuan berhasil dikirim ulang.');
    }

    /**
     * Aksi delegasi persetujuan ke user lain.
     */
    public function delegate(WorkflowRequest $workflowRequest, Request $request)
    {
        $currentApproval = $workflowRequest->currentApproval;
        if (! $currentApproval) {
            abort(400, 'Langkah persetujuan aktif tidak ditemukan.');
        }

        $isAuthorized = (in_array($currentApproval->role, auth()->user()->effectiveRoles(), true) && $currentApproval->delegated_to_id === null)
            || (int) $currentApproval->delegated_to_id === (int) auth()->id();

        if (! $isAuthorized) {
            abort(403, 'Anda tidak berwenang mendelegasikan langkah persetujuan ini.');
        }

        $request->validate([
            'delegatee_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($workflowRequest) {
                    if ((int) $value === (int) $workflowRequest->requester_id) {
                        $fail('Persetujuan tidak dapat didelegasikan kepada pemohon (requester) alur kerja.');
                    }
                },
            ],
            'remarks' => 'nullable|string',
        ]);

        $delegatee = User::findOrFail($request->delegatee_id);

        $this->service->delegate($workflowRequest, auth()->user(), $delegatee, $request->remarks);

        return redirect()->route('workflows.pending')
            ->with('success', 'Persetujuan langkah ini berhasil didelegasikan kepada '.$delegatee->name);
    }

    /**
     * Riwayat alur persetujuan yang sudah selesai.
     */
    public function history(Request $request)
    {
        $query = WorkflowRequest::whereIn('status', ['approved', 'rejected', 'cancelled'])
            ->with(['definition', 'requester', 'approvals.approver']);

        if ($request->has('definition_id') && $request->definition_id) {
            $query->where('definition_id', $request->definition_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(10);
        $definitions = WorkflowDefinition::all();

        return view('workflows.history', compact('requests', 'definitions'));
    }

    /**
     * Analytics: SLA compliance, response times, bottlenecks.
     */
    public function analytics()
    {
        // 1. SLA Compliance
        $pendingOverdue = WorkflowRequest::whereIn('status', ['pending', 'in_review'])
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->count();
        $pendingOnTime = WorkflowRequest::whereIn('status', ['pending', 'in_review'])
            ->where(function ($q) {
                $q->whereNull('due_at')->orWhere('due_at', '>=', now());
            })
            ->count();

        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $avgRequestTimeRaw = 'avg(strftime("%s", workflow_requests.updated_at) - strftime("%s", workflow_requests.created_at)) / 3600';
            $avgRoleTimeRaw = 'avg(strftime("%s", decided_at) - strftime("%s", created_at)) / 3600';
        } else {
            // MySQL, PostgreSQL fallback
            $avgRequestTimeRaw = 'avg(TIMESTAMPDIFF(SECOND, workflow_requests.created_at, workflow_requests.updated_at)) / 3600';
            $avgRoleTimeRaw = 'avg(TIMESTAMPDIFF(SECOND, created_at, decided_at)) / 3600';
        }

        // 2. Average response time per definition type
        $avgTimes = WorkflowRequest::whereIn('status', ['approved', 'rejected'])
            ->join('workflow_definitions', 'workflow_requests.definition_id', '=', 'workflow_definitions.id')
            ->select('workflow_definitions.name', DB::raw("{$avgRequestTimeRaw} as avg_hours"))
            ->groupBy('workflow_definitions.name')
            ->get();

        // 3. Bottleneck Analysis: Average approval time per Step/Role
        $roleTimes = WorkflowApproval::where('decision', '!=', 'pending')
            ->select('role', DB::raw("{$avgRoleTimeRaw} as avg_hours"))
            ->groupBy('role')
            ->orderByDesc('avg_hours')
            ->get();

        return view('workflows.analytics', compact('pendingOverdue', 'pendingOnTime', 'avgTimes', 'roleTimes'));
    }
}
