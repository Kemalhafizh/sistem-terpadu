<?php

namespace App\Support\Access;

use Illuminate\Support\Str;

/**
 * How a role is called on screen, the same for web and app. Every spelling of
 * a role (student/siswa, parent/orang_tua/…) gets one label, so a client never
 * has to guess from the raw role string.
 */
final class RoleLabel
{
    /** Roles with a name of their own; the text is in lang/{id,en,ar}/roles.php. */
    private const KNOWN = [
        'superadmin', 'admin', 'kepala_sekolah', 'waka_kurikulum', 'waka_kesiswaan', 'waka_sarpras',
        'waka_humas', 'staf_tu', 'bendahara', 'bk', 'wali_kelas', 'petugas_uks', 'operator', 'merchant', 'alumni',
    ];

    public static function of(?string $role): string
    {
        $role = (string) $role;

        // In the user's language (lang/{id,en,ar}/roles.php), for the web and the app alike.
        return match (true) {
            in_array($role, StudentDataAccess::STUDENT_ROLES, true) => __('roles.student'),
            in_array($role, StudentDataAccess::PARENT_ROLES, true) => __('roles.parent'),
            in_array($role, StudentDataAccess::TEACHER_ROLES, true) => __('roles.teacher'),
            $role === 'kepsek' => __('roles.kepala_sekolah'),
            in_array($role, self::KNOWN, true) => __("roles.{$role}"),
            // A role added later still reads as words, not as a snake_case key.
            default => Str::headline($role),
        };
    }
}
