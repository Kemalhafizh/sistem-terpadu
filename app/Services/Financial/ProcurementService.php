<?php

namespace App\Services\Financial;

use App\Models\GoodsReceivedNote;
use App\Models\ProcurementRequest;
use App\Models\PurchaseOrder;
use Illuminate\Support\Str;

class ProcurementService
{
    public function createRequest(array $data, int $userId): ProcurementRequest
    {
        return ProcurementRequest::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'department' => $data['department'],
            'requested_by' => $userId,
            'urgency' => $data['urgency'] ?? 'normal',
            'estimated_budget' => $data['estimated_budget'],
            'status' => 'draft',
        ]);
    }

    public function submitForApproval(ProcurementRequest $request): ProcurementRequest
    {
        $request->update(['status' => 'submitted']);

        return $request;
    }

    public function approveRequest(ProcurementRequest $request, int $approverId, ?string $notes = null): ProcurementRequest
    {
        $request->update([
            'status' => 'approved',
            'approved_by' => $approverId,
            'approval_notes' => $notes,
        ]);

        return $request;
    }

    public function rejectRequest(ProcurementRequest $request, int $approverId, string $notes): ProcurementRequest
    {
        $request->update([
            'status' => 'rejected',
            'approved_by' => $approverId,
            'approval_notes' => $notes,
        ]);

        return $request;
    }

    public function createPurchaseOrder(ProcurementRequest $procurementRequest, array $poData, int $userId): PurchaseOrder
    {
        $items = $poData['items'] ?? [];
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ($item['qty'] * $item['unit_price']);
        }
        $tax = $poData['tax_amount'] ?? 0;
        $total = $subtotal + $tax;

        $poNumber = 'PO-'.strtoupper(Str::random(6)).'-'.date('Ymd');

        $po = PurchaseOrder::create([
            'procurement_request_id' => $procurementRequest->id,
            'po_number' => $poNumber,
            'vendor_name' => $poData['vendor_name'],
            'vendor_contact' => $poData['vendor_contact'] ?? null,
            'items' => $items,
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'total_amount' => $total,
            'delivery_date' => $poData['delivery_date'],
            'status' => 'issued',
            'issued_by' => $userId,
        ]);

        $procurementRequest->update(['status' => 'ordered']);

        return $po;
    }

    public function recordGoodsReceived(PurchaseOrder $po, array $grnData, int $userId): GoodsReceivedNote
    {
        $grnNumber = 'GRN-'.strtoupper(Str::random(6)).'-'.date('Ymd');
        $isComplete = $grnData['is_complete'] ?? true;

        $grn = GoodsReceivedNote::create([
            'purchase_order_id' => $po->id,
            'grn_number' => $grnNumber,
            'received_by' => $userId,
            'received_date' => $grnData['received_date'] ?? now(),
            'items_received' => $grnData['items_received'] ?? [],
            'condition_notes' => $grnData['condition_notes'] ?? 'Diterima dalam kondisi baik.',
            'is_complete' => $isComplete,
        ]);

        $po->update([
            'status' => $isComplete ? 'delivered' : 'partial_delivery',
        ]);

        if ($isComplete) {
            $po->procurementRequest->update(['status' => 'received']);
        }

        return $grn;
    }

    public function completeProcurement(ProcurementRequest $request): ProcurementRequest
    {
        $request->update(['status' => 'completed']);
        if ($request->purchaseOrder) {
            $request->purchaseOrder->update(['status' => 'completed']);
        }

        return $request;
    }
}
