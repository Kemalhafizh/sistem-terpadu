<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Intercept users who still have the default password and force them
     * to change it before accessing any other page.
     *
     * Allowed routes: the force-change-password page itself, logout, and profile update.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            $allowed = [
                'password.force-change',
                'password.force-change.update',
                'logout',
            ];

            if (! in_array($request->route()?->getName(), $allowed, true)) {
                return redirect()->route('password.force-change')
                    ->with('warning', 'Demi keamanan, silakan ubah password Anda terlebih dahulu.');
            }
        }

        return $next($request);
    }
}
