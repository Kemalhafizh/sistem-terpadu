<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class MobileSsoBridgeController extends Controller
{
    /**
     * Seamless SSO Auto-Login from Native Mobile App to Web Modules
     *
     * Handles instant token validation, sets the web session cookie,
     * and redirects directly into the requested module without double login.
     */
    public function autoLogin(Request $request)
    {
        $token = $request->query('token');
        $redirectUrl = (string) $request->query('redirect', '/dashboard');

        $user = null;

        // 1. Check Sanctum Personal Access Token if provided
        if (is_string($token) && $token !== '') {
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken && $accessToken->tokenable instanceof User) {
                if (! $accessToken->expires_at || $accessToken->expires_at->isFuture()) {
                    $user = $accessToken->tokenable;
                }
            }
        }

        // 2. Demo / offline email/role fallback is strictly limited to local dev.
        if (! $user && app()->environment('local')) {
            $email = $request->query('email');
            $role = $request->query('role');

            if (is_string($email) && $email !== '') {
                $user = User::where('email', $email)->first();
            }

            if (! $user && is_string($role) && $role !== '') {
                $user = User::where('role', $role)->first();
            }

            if (! $user) {
                $user = User::where('role', 'student')->first() ?? User::first();
            }
        }

        if ($user && ($user->account_status ?? 'aktif') !== 'nonaktif') {
            Auth::guard('web')->login($user, remember: false);
            $request->session()->regenerate();

            // Clean relative path only to prevent open redirects (e.g. //evil.com).
            $cleanRedirect = str_starts_with($redirectUrl, '/') && ! str_starts_with($redirectUrl, '//')
                ? $redirectUrl
                : '/dashboard';

            return redirect($cleanRedirect);
        }

        return redirect()->route('login')->withErrors([
            'email' => 'Sesi SSO tidak valid atau akun tidak aktif.',
        ]);
    }
}
