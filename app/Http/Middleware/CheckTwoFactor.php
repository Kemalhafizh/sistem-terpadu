<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Bypass 2FA check in local and testing environments for easier development and testing
        if (app()->environment('local', 'testing')) {
            return $next($request);
        }

        if (auth()->check() && $user->two_factor_code) {
            if ($user->two_factor_expires_at->lt(now())) {
                $user->resetTwoFactorCode();
                auth()->logout();

                return redirect()->route('login')->withErrors(['email' => 'Kode verifikasi telah kedaluwarsa. Silakan login kembali.']);
            }

            if (! $request->is('verify-2fa*') && ! $request->is('logout')) {
                return redirect()->route('verify-2fa.index');
            }
        }

        return $next($request);
    }
}
