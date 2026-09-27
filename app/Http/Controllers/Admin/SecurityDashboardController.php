<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedIp;
use App\Models\SecurityThreatLog;
use App\Models\Setting;
use Exception;
use Illuminate\Http\Request;

class SecurityDashboardController extends Controller
{
    /**
     * Dashboard monitoring keamanan real-time & status lockdown.
     */
    public function dashboard()
    {
        $isLockdown = (bool) Setting::get('is_system_lockdown', false);
        $unresolvedCount = SecurityThreatLog::where('is_resolved', false)->count();
        $blockedIpsCount = BlockedIp::count();
        $todayThreatsCount = SecurityThreatLog::whereDate('created_at', today())->count();

        $recentThreats = SecurityThreatLog::with('user')->latest()->take(6)->get();
        $blockedIps = BlockedIp::with('blocker')->latest()->take(10)->get();

        return view('admin.security.dashboard', compact(
            'isLockdown',
            'unresolvedCount',
            'blockedIpsCount',
            'todayThreatsCount',
            'recentThreats',
            'blockedIps'
        ));
    }

    /**
     * Forensic Audit Trail & Threat Logs table.
     */
    public function threatLogs(Request $request)
    {
        $severityFilter = $request->get('severity');
        $resolvedFilter = $request->get('resolved');

        $logsQuery = SecurityThreatLog::with('user')
            ->when($severityFilter, function ($query, $severity) {
                $query->where('severity', $severity);
            })
            ->when($resolvedFilter !== null && $resolvedFilter !== '', function ($query) use ($resolvedFilter) {
                $query->where('is_resolved', $resolvedFilter === '1');
            })
            ->latest();

        $logs = $logsQuery->paginate(20);

        return view('admin.security.logs', compact('logs', 'severityFilter', 'resolvedFilter'));
    }

    /**
     * Toggle status System Lockdown Darurat.
     */
    public function toggleLockdown(Request $request)
    {
        try {
            $currentStatus = (bool) Setting::get('is_system_lockdown', false);
            $newStatus = ! $currentStatus;

            Setting::set('is_system_lockdown', $newStatus ? '1' : '0');

            // Log threat event
            SecurityThreatLog::create([
                'user_id' => auth()->id(),
                'event_type' => $newStatus ? 'system_lockdown_enabled' : 'system_lockdown_disabled',
                'severity' => 'critical',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'payload_data' => [
                    'action' => $newStatus ? 'LOCKDOWN_ACTIVATED' : 'LOCKDOWN_DEACTIVATED',
                    'triggered_by' => auth()->user()->name ?? 'Admin',
                ],
                'is_resolved' => true,
            ]);

            $msg = $newStatus
                ? 'SYSTEM LOCKDOWN BERHASIL DIAKTIFKAN! Akses publik dinonaktifkan sementara.'
                : 'System Lockdown telah dinonaktifkan. Akses sistem kembali normal.';

            return redirect()->route('admin.security.index')->with('success', $msg);
        } catch (Exception $e) {
            return back()->with('error', 'Gagal merubah status lockdown: '.$e->getMessage());
        }
    }

    /**
     * Blokir IP address tertentu dari akses web.
     */
    public function blockIp(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'required|string|max:255',
        ]);

        try {
            BlockedIp::firstOrCreate(
                ['ip_address' => $request->ip_address],
                [
                    'reason' => $request->reason,
                    'blocked_by' => auth()->id(),
                ]
            );

            SecurityThreatLog::create([
                'user_id' => auth()->id(),
                'event_type' => 'manual_ip_block',
                'severity' => 'high',
                'ip_address' => $request->ip_address,
                'user_agent' => $request->userAgent(),
                'payload_data' => [
                    'reason' => $request->reason,
                    'blocked_by' => auth()->user()->name ?? 'Admin',
                ],
                'is_resolved' => true,
            ]);

            return redirect()->route('admin.security.index')
                ->with('success', 'IP Address '.$request->ip_address.' berhasil diblokir dari sistem!');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memblokir IP: '.$e->getMessage());
        }
    }

    /**
     * Buka blokir IP address.
     */
    public function unblockIp($id)
    {
        try {
            $blockedIp = BlockedIp::findOrFail($id);
            $ip = $blockedIp->ip_address;
            $blockedIp->delete();

            return redirect()->route('admin.security.index')
                ->with('success', 'Blokir untuk IP Address '.$ip.' berhasil dibuka!');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membuka blokir IP: '.$e->getMessage());
        }
    }

    /**
     * Tandai log ancaman keamanan sebagai selesai/diinvestigasi.
     */
    public function resolveThreat($id)
    {
        try {
            $threat = SecurityThreatLog::findOrFail($id);
            $threat->update(['is_resolved' => true]);

            return back()->with('success', 'Status ancaman keamanan berhasil ditandai Selesai (Resolved).');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memperbarui status: '.$e->getMessage());
        }
    }
}
