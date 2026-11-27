<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\AssetMaintenanceTicket;
use App\Services\Sarpras\AssetMaintenanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssetMaintenanceController extends Controller
{
    public function __construct(
        private readonly AssetMaintenanceService $maintenanceService,
    ) {}

    /**
     * Unified Sarpras Maintenance & QR Damage Tickets hub (`ALUR_KERJA.md` I3).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        if (! $this->maintenanceService->canViewSarpras($user)) {
            abort(403);
        }

        $priorityFilter = $request->filled('priority') ? (string) $request->string('priority') : null;
        $statusFilter = $request->filled('ticket_status') ? (string) $request->string('ticket_status') : null;

        $maintenances = AssetMaintenance::with(['asset', 'handler'])->latest()->paginate(15);
        $assets = Asset::with('room')->orderBy('name')->get();

        $allTickets = AssetMaintenanceTicket::with(['asset.room', 'reporter', 'handler', 'resolver'])
            ->when($priorityFilter, fn ($q) => $q->where('priority', $priorityFilter))
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))
            ->latest('id')
            ->get();

        $ticketsByStatus = [
            'pending' => $allTickets->where('status', AssetMaintenanceTicket::STATUS_PENDING)->values(),
            'investigating' => $allTickets->where('status', AssetMaintenanceTicket::STATUS_INVESTIGATING)->values(),
            'repairing' => $allTickets->where('status', AssetMaintenanceTicket::STATUS_REPAIRING)->values(),
            'resolved' => $allTickets->where('status', AssetMaintenanceTicket::STATUS_RESOLVED)->values(),
            'scrapped' => $allTickets->where('status', AssetMaintenanceTicket::STATUS_SCRAPPED)->values(),
        ];

        $ticketStats = [
            'open_count' => $allTickets->whereIn('status', [
                AssetMaintenanceTicket::STATUS_PENDING,
                AssetMaintenanceTicket::STATUS_INVESTIGATING,
                AssetMaintenanceTicket::STATUS_REPAIRING,
            ])->count(),
            'resolved_count' => $allTickets->where('status', AssetMaintenanceTicket::STATUS_RESOLVED)->count(),
            'scrapped_count' => $allTickets->where('status', AssetMaintenanceTicket::STATUS_SCRAPPED)->count(),
        ];

        $canManage = $this->maintenanceService->canManageSarpras($user);

        return view('assets.maintenances.index', compact(
            'maintenances',
            'assets',
            'allTickets',
            'ticketsByStatus',
            'ticketStats',
            'priorityFilter',
            'statusFilter',
            'canManage',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'maintenance_date' => ['required', 'date'],
            'next_maintenance_date' => ['nullable', 'date', 'after:maintenance_date'],
            'type' => ['required', 'in:preventif,korektif,upgrade'],
            'description' => ['required', 'string'],
            'cost' => ['required', 'numeric', 'min:0'],
            'vendor' => ['nullable', 'string', 'max:160'],
            'status' => ['required', 'in:scheduled,in_progress,completed'],
        ]);

        $this->maintenanceService->createScheduledMaintenance($request->user(), $validated);

        return back()->with('success', __('Jadwal perawatan berhasil disimpan.'));
    }

    public function update(Request $request, AssetMaintenance $maintenance): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:scheduled,in_progress,completed'],
            'next_maintenance_date' => ['nullable', 'date'],
            'cost' => ['required', 'numeric', 'min:0'],
            'description' => ['required', 'string'],
        ]);

        $this->maintenanceService->updateScheduledMaintenance($maintenance, $request->user(), $validated);

        return back()->with('success', __('Data perawatan berhasil diperbarui.'));
    }

    /**
     * QR Scan quick-report page open to all authenticated school members (`GET assets/scan/{asset}`).
     */
    public function scan(Asset $asset): View
    {
        $asset->loadMissing(['room', 'category']);

        return view('assets.maintenance.scan', compact('asset'));
    }

    /**
     * Submit QR damage report (`POST assets/scan/{asset}/report`) with private photo evidence.
     */
    public function storeDamageReport(Request $request, Asset $asset): RedirectResponse
    {
        $validated = $request->validate([
            'issue_description' => ['required', 'string', 'min:10', 'max:2000'],
            'priority' => ['required', Rule::in(AssetMaintenanceTicket::PRIORITIES)],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $this->maintenanceService->submitDamageReport(
            $asset,
            $request->user(),
            $validated,
            $request->file('photo')
        );

        return back()->with('success', __('Laporan kerusakan aset berhasil dikirim ke tim Sarpras.'));
    }

    public function showTicket(Request $request, AssetMaintenanceTicket $ticket): View
    {
        if (! $this->maintenanceService->canViewSarpras($request->user()) && (int) $ticket->reported_by !== (int) $request->user()->id) {
            abort(403);
        }

        $ticket->load(['asset.room', 'reporter', 'handler', 'resolver']);

        return view('assets.maintenance.ticket-detail', compact('ticket'));
    }

    public function updateTicketStatus(Request $request, AssetMaintenanceTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(AssetMaintenanceTicket::STATUSES)],
            'resolution_notes' => ['nullable', 'string', 'max:2000'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'vendor' => ['nullable', 'string', 'max:160'],
        ]);

        $this->maintenanceService->transitionTicketStatus(
            $ticket,
            $request->user(),
            $validated['status'],
            $validated
        );

        return redirect()
            ->route('assets.maintenances.index')
            ->with('success', __('Status tiket kerusakan dan kondisi aset berhasil diperbarui.'));
    }

    public function downloadTicketAttachment(Request $request, AssetMaintenanceTicket $ticket)
    {
        return $this->maintenanceService->streamTicketAttachment($ticket, $request->user());
    }

    public function qrLabels(Request $request): View
    {
        if (! $this->maintenanceService->canViewSarpras($request->user())) {
            abort(403);
        }

        $assets = Asset::with('room')->orderBy('asset_code')->get();

        return view('assets.maintenance.qr-print', compact('assets'));
    }
}
