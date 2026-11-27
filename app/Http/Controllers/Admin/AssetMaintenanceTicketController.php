<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetMaintenanceTicket;
use App\Services\Sarpras\AssetMaintenanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Thin backward-compatible route adapter delegating to `AssetMaintenanceService`.
 * Canonical I3 routes live on `AssetMaintenanceController` (`assets.scan`, `assets.maintenances.*`).
 */
class AssetMaintenanceTicketController extends Controller
{
    public function __construct(
        private readonly AssetMaintenanceService $maintenanceService,
    ) {}

    public function scanQr(string $uuid): View
    {
        $asset = Asset::where('uuid', $uuid)->with(['room', 'category'])->firstOrFail();

        return view('assets.maintenance.scan', compact('asset'));
    }

    public function storeTicket(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'issue_description' => ['required', 'string', 'min:10', 'max:2000'],
            'priority' => ['required', Rule::in(AssetMaintenanceTicket::PRIORITIES)],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $asset = Asset::findOrFail((int) $validated['asset_id']);

        $this->maintenanceService->submitDamageReport(
            $asset,
            $request->user(),
            $validated,
            $request->file('photo')
        );

        return back()->with('success', __('Laporan kerusakan berhasil dikirim. Terima kasih atas partisipasi Anda.'));
    }

    public function index(Request $request): View
    {
        $priorityFilter = $request->get('priority');

        $allTickets = AssetMaintenanceTicket::with(['asset.room', 'reporter'])
            ->when($priorityFilter, fn ($q, $priority) => $q->where('priority', $priority))
            ->latest()
            ->get();

        $pending = $allTickets->where('status', AssetMaintenanceTicket::STATUS_PENDING);
        $investigating = $allTickets->where('status', AssetMaintenanceTicket::STATUS_INVESTIGATING);
        $repairing = $allTickets->where('status', AssetMaintenanceTicket::STATUS_REPAIRING);
        $resolved = $allTickets->where('status', AssetMaintenanceTicket::STATUS_RESOLVED);
        $scrapped = $allTickets->where('status', AssetMaintenanceTicket::STATUS_SCRAPPED);

        return view('assets.maintenance.index', compact(
            'pending',
            'investigating',
            'repairing',
            'resolved',
            'scrapped',
            'priorityFilter'
        ));
    }

    public function showTicket(string|int $id): View
    {
        $ticket = $this->maintenanceService->findTicketByKey($id);
        $ticket->load(['asset.room', 'reporter', 'handler', 'resolver']);

        return view('assets.maintenance.ticket-detail', compact('ticket'));
    }

    public function updateTicketStatus(string|int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(AssetMaintenanceTicket::STATUSES)],
            'resolution_notes' => ['nullable', 'string', 'max:2000'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'vendor' => ['nullable', 'string', 'max:160'],
        ]);

        $ticket = $this->maintenanceService->findTicketByKey($id);
        $this->maintenanceService->transitionTicketStatus(
            $ticket,
            $request->user(),
            $validated['status'],
            $validated
        );

        return redirect()->route('admin.assets.maintenance.index')
            ->with('success', __('Status tiket perbaikan aset berhasil diperbarui.'));
    }

    public function generateQrLabels(): View
    {
        $assets = Asset::with('room')->orderBy('asset_code')->get();

        return view('assets.maintenance.qr-print', compact('assets'));
    }
}
