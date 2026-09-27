<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DisasterRecoveryService;
use Illuminate\Http\Request;

class DisasterRecoveryController extends Controller
{
    /**
     * Display Disaster Recovery & Database Snapshot Dashboard.
     */
    public function index(DisasterRecoveryService $drService)
    {
        $snapshots = $drService->getSnapshots();
        $auditResult = $drService->runIntegrityAudit();

        $totalStorageBytes = array_sum(array_column($snapshots, 'size_bytes'));
        $totalStorageFormatted = round($totalStorageBytes / 1024 / 1024, 2).' MB';

        return view('admin.security.disaster_recovery', compact(
            'snapshots',
            'auditResult',
            'totalStorageFormatted'
        ));
    }

    /**
     * Trigger new snapshot creation.
     */
    public function createSnapshot(Request $request, DisasterRecoveryService $drService)
    {
        $request->validate([
            'label' => 'nullable|string|alpha_dash|max:50',
        ]);

        $label = $request->input('label', 'manual');
        $meta = $drService->createSnapshot($label);

        return back()->with('success', "Snapshot database [{$meta['filename']}] berhasil dibuat dan diverifikasi!");
    }

    /**
     * Verify snapshot SHA-256 checksum integrity.
     */
    public function verifySnapshot(Request $request, DisasterRecoveryService $drService)
    {
        $request->validate([
            'filename' => 'required|string',
        ]);

        $isValid = $drService->verifyChecksum($request->filename);

        if ($isValid) {
            return back()->with('success', "Verifikasi Kriptografi SHA-256 [{$request->filename}]: INTEGRITAS SAH (UNTOUCHED)!");
        } else {
            return back()->with('error', "Verifikasi Kriptografi SHA-256 [{$request->filename}]: FAIL / BERKAS RUSAK!");
        }
    }

    /**
     * Restore database from snapshot.
     */
    public function restoreSnapshot(Request $request, DisasterRecoveryService $drService)
    {
        $request->validate([
            'filename' => 'required|string',
        ]);

        try {
            $drService->restoreSnapshot($request->filename);

            return back()->with('success', "Sistem berhasil dipulihkan (Point-In-Time Restore) dari snapshot [{$request->filename}]!");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memulihkan database: '.$e->getMessage());
        }
    }

    /**
     * Delete snapshot file.
     */
    public function deleteSnapshot(Request $request, DisasterRecoveryService $drService)
    {
        $request->validate([
            'filename' => 'required|string',
        ]);

        $drService->deleteSnapshot($request->filename);

        return back()->with('success', "Snapshot [{$request->filename}] berhasil dihapus dari media penyimpanan.");
    }

    /**
     * Trigger manual database integrity audit.
     */
    public function runAudit(DisasterRecoveryService $drService)
    {
        $audit = $drService->runIntegrityAudit();

        if ($audit['status'] === 'HEALTHY') {
            return back()->with('success', 'Audit Integritas Database Selesai: Struktur database & foreign key 100% konsisten (HEALTHY)!');
        } else {
            return back()->with('error', 'Audit Integritas Database Menemukan Masalah: Terdeteksi kecacatan data / foreign key error!');
        }
    }
}
