<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActiveSession;
use App\Models\LoginAttempt;
use App\Services\SecurityAuditService;
use App\Services\SecurityMonitorService;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    protected $securityService;

    public function __construct(SecurityMonitorService $securityService)
    {
        $this->securityService = $securityService;
    }

    public function dashboard()
    {
        $stats = $this->securityService->getSecurityDashboardData();
        $trend = $this->securityService->getLoginTrend(30);
        $recentAttempts = LoginAttempt::orderBy('created_at', 'desc')->limit(20)->get();

        return view('admin.security.dashboard', compact('stats', 'trend', 'recentAttempts'));
    }

    public function sessions()
    {
        $sessions = ActiveSession::with('user')->orderBy('last_activity_at', 'desc')->paginate(15);

        return view('admin.security.sessions', compact('sessions'));
    }

    public function terminateSession(Request $request, $id)
    {
        try {
            $session = ActiveSession::findOrFail($id);

            // Check if it's the current user's session and if they terminate it, they'll be logged out
            $isCurrentSession = ($session->session_id === $request->session()->getId());

            $session->delete();

            if ($isCurrentSession) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('success', 'Sesi Anda sendiri telah diterminasi secara paksa.');
            }

            return back()->with('success', 'Sesi aktif pengguna berhasil diterminasi secara paksa.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menerminasi sesi: '.$e->getMessage());
        }
    }

    public function audit(SecurityAuditService $auditService)
    {
        $report = $auditService->runFullAudit();

        return view('admin.security.audit', compact('report'));
    }
}
