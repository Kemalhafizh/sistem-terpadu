<?php

namespace App\Http\Middleware;

use App\Models\User;
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
     * Only `superadmin` passes every role check. `admin` passes when the route
     * lists `admin`, or when it lists a leadership role. Users holding active
     * positions (`PositionAssignment`) combine their base account role and
     * active position roles.
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

        if (self::allowsUser($user, $roles)) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }

    /** @param  list<string>  $roles */
    public static function allowsUser(User $user, array $roles): bool
    {
        if (($user->account_status ?? 'aktif') === 'nonaktif') {
            return false;
        }

        foreach ($user->effectiveRoles() as $effectiveRole) {
            if (self::allows($effectiveRole, $roles)) {
                return true;
            }
        }

        return false;
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
