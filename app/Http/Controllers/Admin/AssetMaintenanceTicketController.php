<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetMaintenanceTicket;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;

class AssetMaintenanceTicketController extends Controller
{
    /**
     * Scan QR Code endpoint. Redirects users to report page.
     */
    public function scanQr($uuid)
    {
        $asset = Asset::where('uuid', $uuid)->with('room')->firstOrFail();
        return view('assets.maintenance.scan', compact('asset'));
    }

    /**
     * Submit a new maintenance ticket.
     */
    public function storeTicket(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'issue_description' => 'required|string|min:10',
            'priority' => 'required|in:low,medium,high,critical',
            'photo' => 'nullable|image|max:2048',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photo_evidences', 'public');
        }

        try {
            AssetMaintenanceTicket::create([
                'asset_id' => $request->asset_id,
                'reported_by' => auth()->id(),
                'issue_description' => $request->issue_description,
                'priority' => $request->priority,
                'status' => 'pending',
                'photo_evidence' => $photoPath,
            ]);

            return back()->with('success', 'Laporan kerusakan berhasil dikirim. Terima kasih atas partisipasi Anda.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal mengirim laporan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Kanban Board dashboard for admin sarpras.
     */
    public function index(Request $request)
    {
        $priorityFilter = $request->get('priority');

        $ticketsQuery = AssetMaintenanceTicket::with(['asset.room', 'reporter'])
            ->when($priorityFilter, function ($query, $priority) {
                $query->where('priority', $priority);
            })
            ->latest();

        $allTickets = $ticketsQuery->get();

        // Group by status for Kanban columns
        $pending = $allTickets->where('status', 'pending');
        $investigating = $allTickets->where('status', 'investigating');
        $repairing = $allTickets->where('status', 'repairing');
        $resolved = $allTickets->where('status', 'resolved');
        $scrapped = $allTickets->where('status', 'scrapped');

        return view('assets.maintenance.index', compact(
            'pending',
            'investigating',
            'repairing',
            'resolved',
            'scrapped',
            'priorityFilter'
        ));
    }

    /**
     * Display ticket details and resolution form.
     */
    public function showTicket($id)
    {
        $ticket = AssetMaintenanceTicket::with(['asset.room', 'reporter', 'resolver'])->findOrFail($id);
        return view('assets.maintenance.ticket-detail', compact('ticket'));
    }

    /**
     * Update ticket status & resolution.
     */
    public function updateTicketStatus($id, Request $request)
    {
        $request->validate([
            'status' => 'required|in:pending,investigating,repairing,resolved,scrapped',
            'resolution_notes' => 'nullable|string',
        ]);

        try {
            $ticket = AssetMaintenanceTicket::findOrFail($id);
            $asset = Asset::findOrFail($ticket->asset_id);

            $updateData = [
                'status' => $request->status,
                'resolution_notes' => $request->resolution_notes,
            ];

            if (in_array($request->status, ['resolved', 'scrapped'])) {
                $updateData['resolved_by'] = auth()->id();
                $updateData['resolved_at'] = Carbon::now();

                // Sync parent asset condition/status
                if ($request->status === 'resolved') {
                    $asset->update(['condition' => 'good', 'status' => 'available']);
                } else {
                    $asset->update(['condition' => 'broken', 'status' => 'lost']);
                }
            } else {
                // If moved back to repairing/investigating
                $asset->update(['status' => 'repairing']);
            }

            $ticket->update($updateData);

            return redirect()->route('admin.assets.maintenance.index')
                ->with('success', 'Status tiket perbaikan aset berhasil diperbarui.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }

    /**
     * Generate large QR labels sheet (Print A4 view).
     */
    public function generateQrLabels()
    {
        $assets = Asset::with('room')->orderBy('asset_code')->get();
        return view('assets.maintenance.qr-print', compact('assets'));
    }
}
