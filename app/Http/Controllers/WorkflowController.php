<?php

namespace App\Http\Controllers;

use App\Models\WorkflowRequest;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class WorkflowController extends Controller
{
    protected $service;

    public function __construct(WorkflowService $service)
    {
        $this->service = $service;
    }

    /**
     * Tampilkan daftar pengajuan yang sedang menunggu persetujuan pengguna saat ini.
     */
    public function pending()
    {
        $requests = $this->service->getPendingForUser(auth()->user());
        return view('workflows.pending', compact('requests'));
    }

    /**
     * Tampilkan daftar pengajuan yang diajukan oleh pengguna saat ini.
     */
    public function myRequests()
    {
        $requests = WorkflowRequest::where('requester_id', auth()->id())
            ->with(['definition', 'approvals.approver'])
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

        $workflowRequest->load(['definition', 'requester', 'approvals.approver', 'requestable']);
        
        return view('workflows.show', [
            'request' => $workflowRequest
        ]);
    }

    /**
     * Aksi persetujuan untuk langkah saat ini.
     */
    public function approve(WorkflowRequest $workflowRequest, Request $request)
    {
        $currentApproval = $workflowRequest->currentApproval;
        if (!$currentApproval || $currentApproval->role !== auth()->user()->role) {
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
        if (!$currentApproval || $currentApproval->role !== auth()->user()->role) {
            abort(403, 'Anda tidak berwenang memberikan tindakan pada langkah ini.');
        }

        $request->validate([
            'remarks' => 'required|string|min:5',
        ]);

        $this->service->reject($workflowRequest, auth()->user(), $request->remarks);

        return redirect()->route('workflows.pending')
            ->with('success', 'Pengajuan telah ditolak dan dihentikan.');
    }
}
