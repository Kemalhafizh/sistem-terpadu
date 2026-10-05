<?php

namespace App\Support\Access;

use App\Http\Middleware\RoleMiddleware;

/**
 * Role groups for `role:` middleware, built from the same constants the rest
 * of the app uses, so a route file never spells roles out by hand:
 *
 *     ->middleware(Roles::only(StudentDataAccess::TEACHER_ROLES, Roles::LEADERSHIP, 'admin'))
 */
final class Roles
{
    /** The principal under both spellings. */
    public const PRINCIPAL = ['kepala_sekolah', 'kepsek'];

    /** Principal and vice-principals (an `admin` passes any route that lists one, see RoleMiddleware). */
    public const LEADERSHIP = RoleMiddleware::LEADERSHIP_ROLES;

    /**
     * Everyone who belongs to the school: students, parents, teachers, staff,
     * leadership and admin (not outside accounts such as `merchant` or `alumni`).
     */
    public const MEMBERS = [
        ...StudentDataAccess::STUDENT_ROLES,
        ...StudentDataAccess::PARENT_ROLES,
        ...StudentDataAccess::TEACHER_ROLES,
        ...StudentDataAccess::STAFF_ROLES,
        ...RoleMiddleware::LEADERSHIP_ROLES,
        'admin',
    ];

    /**
     * `role:a,b,c` for the given role names and role lists, duplicates removed.
     *
     * @param  string|list<string>  ...$roles
     */
    public static function only(string|array ...$roles): string
    {
        $flat = [];
        array_walk_recursive($roles, function (string $role) use (&$flat) {
            $flat[$role] = true;
        });

        return 'role:'.implode(',', array_keys($flat));
    }
}
