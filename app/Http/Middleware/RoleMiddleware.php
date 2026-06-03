<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Accepts a variadic list of allowed roles.
     * Usage in routes: ->middleware('role:admin,guru,staf_tu')
     *
     * Note: admin/superadmin bypass is already handled by Gate::before()
     * in AppServiceProvider, but we explicitly include them in route groups
     * for clarity and defense-in-depth.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Admin & Superadmin bypass all role restrictions for ease of maintenance
        if ($user->role === 'admin' || $user->role === 'superadmin') {
            return $next($request);
        }

        if (! in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
