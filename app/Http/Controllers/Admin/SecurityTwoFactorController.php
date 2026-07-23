<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TotpSecurityService;
use Illuminate\Http\Request;

class SecurityTwoFactorController extends Controller
{
    /**
     * Display Two-Factor Authentication & Active Sessions Control Panel.
     */
    public function index(TotpSecurityService $totpService)
    {
        $user = auth()->user();
        $is2faEnabled = $user->hasTotpEnabled();

        $secret = null;
        $qrCodeUrl = null;

        if (! $is2faEnabled) {
            // Generate draft secret for setup
            $secret = session('totp_draft_secret');
            if (! $secret) {
                $secret = $totpService->generateSecret();
                session(['totp_draft_secret' => $secret]);
            }
            $qrCodeUrl = $totpService->generateQrCodeUrl($user->email, $secret);
        }

        $activeSession = [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'fingerprint' => session('session_fingerprint'),
            'last_activity' => now()->format('d M Y H:i:s'),
        ];

        $isElevated = session('elevated_until') && now()->timestamp <= session('elevated_until');

        return view('admin.security.two_factor', compact(
            'user',
            'is2faEnabled',
            'secret',
            'qrCodeUrl',
            'activeSession',
            'isElevated'
        ));
    }

    /**
     * Confirm and activate 2FA with 6-digit TOTP code.
     */
    public function confirm(Request $request, TotpSecurityService $totpService)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->user();
        $secret = session('totp_draft_secret');

        if (! $secret) {
            return back()->with('error', 'Sesi penyiapan 2FA telah kadaluarsa. Silakan muat ulang halaman.');
        }

        if (! $totpService->verifyCode($secret, $request->code)) {
            return back()->with('error', 'Kode verifikasi TOTP salah atau telah kadaluarsa. Silakan periksa aplikasi Authenticator Anda.');
        }

        // Generate recovery codes
        $recoveryCodes = $totpService->generateRecoveryCodes();

        $user->totp_secret = $secret;
        $user->totp_enabled_at = now();
        $user->totp_recovery_codes = $recoveryCodes;
        $user->save();

        session()->forget('totp_draft_secret');

        return back()->with('success', 'Verifikasi Dua Faktor (2FA) berhasil diaktifkan untuk akun Anda!');
    }

    /**
     * Disable 2FA after password confirmation.
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = auth()->user();
        $user->totp_secret = null;
        $user->totp_enabled_at = null;
        $user->totp_recovery_codes = null;
        $user->save();

        return back()->with('success', 'Verifikasi Dua Faktor (2FA) berhasil dinonaktifkan.');
    }

    /**
     * Display Step-Up Security Elevation Form.
     */
    public function showElevateForm()
    {
        return view('admin.security.elevate');
    }

    /**
     * Process Step-Up Re-Authentication Elevation.
     */
    public function elevate(Request $request, TotpSecurityService $totpService)
    {
        $user = auth()->user();

        $request->validate([
            'password' => 'required|current_password',
            'totp_code' => 'nullable|string',
        ]);

        if ($user->hasTotpEnabled()) {
            $code = $request->input('totp_code');
            if (empty($code) || ! $totpService->verifyUserTotpOrRecoveryCode($user, $code)) {
                return back()->withErrors(['totp_code' => 'Kode TOTP Authenticator atau Kode Pemulihan tidak valid.']);
            }
        }

        // Elevate session for 15 minutes
        session(['elevated_until' => now()->addMinutes(15)->timestamp]);

        $redirectUrl = session('intended_elevation_url', route('dashboard'));
        session()->forget('intended_elevation_url');

        return redirect($redirectUrl)->with('success', 'Autentikasi Tingkat Tinggi (Step-Up Elevation) berhasil disetujui selama 15 menit.');
    }
}
