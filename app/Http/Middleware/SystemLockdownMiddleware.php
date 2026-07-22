<?php

namespace App\Http\Middleware;

use App\Models\BlockedIp;
use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SystemLockdownMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Cek apakah IP pengguna terdaftar dalam daftar blokir
        $clientIp = $request->ip();
        if (BlockedIp::where('ip_address', $clientIp)->exists()) {
            abort(403, 'Akses IP Anda (' . $clientIp . ') diblokir oleh sistem SISTA karena terindikasi aktivitas mencurigakan.');
        }

        // 2. Cek status System Lockdown Darurat
        $isLockdown = Setting::get('is_system_lockdown', false);

        if ($isLockdown) {
            // Berikan akses khusus hanya untuk rute admin security, login, dan logout
            $allowedPaths = ['admin/security*', 'login', 'logout', 'up'];
            
            $isAllowedRoute = false;
            foreach ($allowedPaths as $path) {
                if ($request->is($path)) {
                    $isAllowedRoute = true;
                    break;
                }
            }

            // Jika user adalah superadmin / admin dan mengakses rute security/auth, izinkan
            $user = auth()->user();
            $isAdmin = $user && in_array($user->role, ['admin', 'superadmin']);

            if (! $isAllowedRoute || (! $isAdmin && ! $request->is('login') && ! $request->is('logout'))) {
                abort(503, 'Sistem SISTA sedang dalam mode Lockdown Darurat Keamanan. Akses publik dinonaktifkan sementara.');
            }
        }

        return $next($request);
    }
}
