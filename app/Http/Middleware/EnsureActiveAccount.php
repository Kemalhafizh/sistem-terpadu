<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAccount
{
    /**
     * Block access and terminate credentials for deactivated user accounts.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?? ($request->bearerToken() ? $request->user('sanctum') : null);

        if ($user && ($user->account_status ?? 'aktif') === 'nonaktif') {
            if ($request->expectsJson() || $request->is('api/*')) {
                $token = method_exists($user, 'currentAccessToken') ? $user->currentAccessToken() : null;
                if ($token instanceof PersonalAccessToken) {
                    $token->delete();
                }

                return response()->json([
                    'success' => false,
                    'code' => 'ACCOUNT_INACTIVE',
                    'message' => 'Akun Anda telah dinonaktifkan. Hubungi administrator sekolah.',
                ], 403);
            }

            Auth::guard('web')->logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda telah dinonaktifkan. Hubungi administrator sekolah.',
            ]);
        }

        return $next($request);
    }
}
