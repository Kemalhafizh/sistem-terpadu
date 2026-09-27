<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\ProcurementRequest;
use App\Models\PurchaseOrder;
use App\Services\Financial\ProcurementService;
use Illuminate\Http\Request;

class ProcurementController extends Controller
{
    public function dashboard()
    {
        $requests = ProcurementRequest::with(['requester', 'approver', 'purchaseOrder'])->latest()->get();

        return view('procurement.dashboard', compact('requests'));
    }

    public function createRequest()
    {
        return view('procurement.create-request');
    }

    public function storeRequest(Request $request, ProcurementService $service)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'department' => 'required|in:academic,administrative,facilities,it,islamic',
            'urgency' => 'required|in:low,normal,high,critical',
            'estimated_budget' => 'required|numeric|min:0',
        ]);

        $procurement = $service->createRequest($validated, auth()->id());

        return redirect()->route('financial.procurement.dashboard')
            ->with('success', 'Permohonan Pengadaan Barang/Jasa berhasil diajukan!');
    }

    public function submitApproval(int $id, ProcurementService $service)
    {
        $req = ProcurementRequest::findOrFail($id);
        $service->submitForApproval($req);

        return back()->with('success', 'Permohonan pengadaan berhasil diajukan untuk persetujuan!');
    }

    public function approve(int $id, Request $request, ProcurementService $service)
    {
        $req = ProcurementRequest::findOrFail($id);
        $notes = $request->input('notes');
        $service->approveRequest($req, auth()->id(), $notes);

        return back()->with('success', 'Permohonan pengadaan telah disetujui!');
    }

    public function reject(int $id, Request $request, ProcurementService $service)
    {
        $req = ProcurementRequest::findOrFail($id);
        $request->validate(['notes' => 'required|string']);
        $service->rejectRequest($req, auth()->id(), $request->input('notes'));

        return back()->with('success', 'Permohonan pengadaan ditolak.');
    }

    public function createPO(int $id)
    {
        $procurementRequest = ProcurementRequest::findOrFail($id);

        return view('procurement.purchase-order', compact('procurementRequest'));
    }

    public function storePO(int $id, Request $request, ProcurementService $service)
    {
        $procurementRequest = ProcurementRequest::findOrFail($id);

        $validated = $request->validate([
            'vendor_name' => 'required|string|max:255',
            'vendor_contact' => 'nullable|string|max:255',
            'delivery_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
        ]);

        $po = $service->createPurchaseOrder($procurementRequest, $validated, auth()->id());

        return redirect()->route('financial.procurement.dashboard')
            ->with('success', "Purchase Order [{$po->po_number}] berhasil diterbitkan!");
    }

    public function recordGRN(int $poId, Request $request, ProcurementService $service)
    {
        $po = PurchaseOrder::findOrFail($poId);

        $validated = $request->validate([
            'received_date' => 'required|date',
            'items_received' => 'required|array',
            'condition_notes' => 'nullable|string',
            'is_complete' => 'nullable|boolean',
        ]);

        $grn = $service->recordGoodsReceived($po, $validated, auth()->id());

        return back()->with('success', "Goods Received Note [{$grn->grn_number}] telah dicatat!");
    }

    public function history()
    {
        $requests = ProcurementRequest::with(['requester', 'purchaseOrder.goodsReceivedNotes'])->latest()->get();

        return view('procurement.history', compact('requests'));
    }
}
