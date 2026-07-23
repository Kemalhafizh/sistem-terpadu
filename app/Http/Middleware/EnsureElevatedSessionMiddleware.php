<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureElevatedSessionMiddleware
{
    /**
     * Require elevated security session (password/2FA re-verification within last 15 minutes) for high-impact actions.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $elevatedUntil = session('elevated_until');

            if (! $elevatedUntil || now()->timestamp > $elevatedUntil) {
                // Store intended URL so user returns back after elevation
                session(['intended_elevation_url' => $request->fullUrl()]);

                return redirect()->route('admin.security.2fa.elevate_form')->with('warning', 'Tindakan ini memerlukan Konfirmasi Keamanan Tingkat Tinggi (Step-Up Elevation).');
            }
        }

        return $next($request);
    }
}
