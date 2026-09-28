<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * School leadership roles. An `admin` (school operator) may open what
     * leadership opens — the principal's and vice-principals' dashboards —
     * because that is part of running the school; see [handle].
     */
    public const LEADERSHIP_ROLES = [
        'kepala_sekolah', 'kepsek',
        'waka_kurikulum', 'waka_kesiswaan', 'waka_sarpras', 'waka_humas',
    ];

    /**
     * Usage in routes: ->middleware('role:guru,bk')
     *
     * Only `superadmin` passes every role check. `admin` used to as well,
     * which let an operator account into student, parent and teacher
     * features (and made `role:` lists that omit admin meaningless). Now an
     * admin passes when the route lists `admin`, or when it lists a
     * leadership role (explicit rule above) — never through a blanket bypass.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                abort(401, 'Silakan masuk terlebih dahulu.');
            }

            return redirect()->route('login');
        }

        if (self::allows($user->role, $roles)) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }

    /** @param  list<string>  $roles */
    public static function allows(?string $role, array $roles): bool
    {
        if ($role === null || $role === '') {
            return false;
        }
        if ($role === 'superadmin' || in_array($role, $roles, true)) {
            return true;
        }

        return $role === 'admin' && array_intersect($roles, self::LEADERSHIP_ROLES) !== [];
    }
}
