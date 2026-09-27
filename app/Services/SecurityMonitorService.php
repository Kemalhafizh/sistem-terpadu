<?php

namespace App\Services;

use App\Models\ActiveSession;
use App\Models\LoginAttempt;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SecurityMonitorService
{
    /**
     * Mendeteksi adanya brute force: >= 5 gagal login dari 1 IP dalam 10 menit.
     */
    public function detectBruteForce(): array
    {
        $tenMinutesAgo = Carbon::now()->subMinutes(10);

        return LoginAttempt::select('ip_address', DB::raw('count(*) as total_failures'))
            ->where('successful', false)
            ->where('created_at', '>=', $tenMinutesAgo)
            ->groupBy('ip_address')
            ->having('total_failures', '>=', 5)
            ->get()
            ->toArray();
    }

    /**
     * Mendapatkan data statistik keamanan untuk SIEM dashboard.
     */
    public function getSecurityDashboardData(): array
    {
        $today = Carbon::today();

        return [
            'total_attempts_today' => LoginAttempt::whereDate('created_at', $today)->count(),
            'successful_today' => LoginAttempt::whereDate('created_at', $today)->where('successful', true)->count(),
            'failed_today' => LoginAttempt::whereDate('created_at', $today)->where('successful', false)->count(),
            'brute_force_alerts' => $this->detectBruteForce(),
            'active_sessions_count' => ActiveSession::count(),
            'top_ips' => LoginAttempt::select('ip_address', DB::raw('count(*) as count'))
                ->groupBy('ip_address')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * Mendapatkan trend login sukses vs gagal harian untuk grafik ApexCharts (30 hari terakhir).
     */
    public function getLoginTrend(int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);

        $trends = LoginAttempt::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(CASE WHEN successful = 1 THEN 1 ELSE 0 END) as success_count'),
            DB::raw('SUM(CASE WHEN successful = 0 THEN 1 ELSE 0 END) as failure_count')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();

        return [
            'dates' => $trends->pluck('date')->toArray(),
            'success' => $trends->pluck('success_count')->map(fn ($v) => (int) $v)->toArray(),
            'failure' => $trends->pluck('failure_count')->map(fn ($v) => (int) $v)->toArray(),
        ];
    }
}
