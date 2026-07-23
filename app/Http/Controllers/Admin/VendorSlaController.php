<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorSlaMetric;
use App\Services\VendorSlaProbeService;

class VendorSlaController extends Controller
{
    /**
     * Display Vendor SLA Scorecard Dashboard.
     */
    public function index(VendorSlaProbeService $probeService)
    {
        $slaSummary = $probeService->getVendorSlaSummary();

        $recentLogs = VendorSlaMetric::query()
            ->orderBy('checked_at', 'desc')
            ->take(20)
            ->get();

        $totalProbes = VendorSlaMetric::count();
        $overallCompliant = collect($slaSummary)->every(fn ($v) => $v['is_sla_compliant']);

        return view('admin.system.vendor_sla', compact(
            'slaSummary',
            'recentLogs',
            'totalProbes',
            'overallCompliant'
        ));
    }

    /**
     * Trigger manual vendor SLA probe test.
     */
    public function probeNow(VendorSlaProbeService $probeService)
    {
        $probeService->probeAllVendors();

        return back()->with('success', 'Pengujian SLA Vendor Pihak Ketiga (Fonnte, Midtrans, Dapodik, EMIS) berhasil diperbarui!');
    }
}
