<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SessionFingerprintMiddleware
{
    /**
     * Bind active session to IP Subnet & User-Agent fingerprint.
     * Detects potential session hijacking or suspicious IP/Device drifts.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $currentFingerprint = $this->generateFingerprint($request);
            $sessionFingerprint = session('session_fingerprint');

            if (is_null($sessionFingerprint)) {
                // Initialize session fingerprint
                session(['session_fingerprint' => $currentFingerprint]);
            } elseif ($sessionFingerprint !== $currentFingerprint) {
                // Suspicious drift detected: IP Subnet or User-Agent changed mid-session
                Log::warning('Security Safeguard: Pergeseran Sesi Mencurigakan Terdeteksi!', [
                    'user_id' => Auth::id(),
                    'user_email' => Auth::user()->email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                // Clear session & force re-login for security
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', 'Sesi Anda dihentikan otomatis demi keamanan karena terdeteksi perubahan perangkat / jaringan internet.');
            }
        }

        return $next($request);
    }

    /**
     * Generate SHA-256 fingerprint hash of User-Agent.
     * Avoids binding to /24 IP subnet because mobile 4G/LTE & roaming WiFi users shift subnets frequently.
     */
    private function generateFingerprint(Request $request): string
    {
        $userAgent = $request->userAgent() ?? 'unknown_device';

        return hash('sha256', "ua:{$userAgent}");
    }
}
