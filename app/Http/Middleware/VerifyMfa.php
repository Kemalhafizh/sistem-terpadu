<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyMfa
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (auth()->check() && $user) {
            // Peran administratif sensitif wajib menggunakan MFA
            $sensitiveRoles = ['admin', 'superadmin', 'staf_keuangan'];
            
            if (in_array($user->role, $sensitiveRoles)) {
                // Memastikan kolom status mfa_enabled aktif
                // Jika MFA belum diset/diaktifkan dan bukan di halaman setup MFA, arahkan ke halaman setup
                if (!$user->mfa_enabled && !$request->is('settings/mfa*') && !$request->is('logout')) {
                    $targetRoute = \Illuminate\Support\Facades\Route::has('settings.mfa.setup') ? 'settings.mfa.setup' : 'verify-2fa.index';
                    return redirect()->route($targetRoute)->with('warning', 'Anda wajib mengaktifkan Multi-Factor Authentication (MFA) sebelum mengakses panel ini.');
                }
                
                // Jika MFA aktif tapi belum terverifikasi di sesi ini
                if ($user->mfa_enabled && !session('mfa_verified') && !$request->is('verify-mfa*') && !$request->is('logout')) {
                    $targetRoute = \Illuminate\Support\Facades\Route::has('verify-mfa.index') ? 'verify-mfa.index' : 'verify-2fa.index';
                    return redirect()->route($targetRoute);
                }
            }
        }

        return $next($request);
    }
}
