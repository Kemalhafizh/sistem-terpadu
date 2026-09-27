<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImmutableAuditLog;
use App\Services\ImmutableAuditLedgerService;
use App\Services\PermissionService;

class RbacAuditController extends Controller
{
    /**
     * Display Immutable Cryptographic Audit Ledger & Enterprise RBAC Dashboard.
     */
    public function index(ImmutableAuditLedgerService $ledgerService, PermissionService $permissionService)
    {
        $logs = ImmutableAuditLog::with('user')->latest('id')->paginate(15);
        $integrity = $ledgerService->verifyChainIntegrity();
        $matrix = $permissionService->getRolePermissionMatrix();

        return view('admin.security.rbac', compact('logs', 'integrity', 'matrix'));
    }

    /**
     * Re-verify full cryptographic SHA-256 Merkle chain integrity.
     */
    public function verifyLedgerIntegrity(ImmutableAuditLedgerService $ledgerService)
    {
        $integrity = $ledgerService->verifyChainIntegrity();

        if ($integrity['is_valid']) {
            return back()->with('success', "Verifikasi Cryptographic Ledger Selesai: 100% Valid! {$integrity['total_logs']} entri log terverifikasi utuh tanpa manipulasi database.");
        } else {
            return back()->with('error', "PERINGATAN BAHAYA INTEGRITAS: Ditemukan {$integrity['tampered_count']} entri log terpotong/dimanipulasi (ID: ".implode(', ', $integrity['tampered_ids']).').');
        }
    }
}
