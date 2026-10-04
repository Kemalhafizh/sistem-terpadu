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
    private const LABELS = [
        'superadmin' => 'Superadmin',
        'admin' => 'Administrator',
        'kepala_sekolah' => 'Kepala Sekolah',
        'kepsek' => 'Kepala Sekolah',
        'waka_kurikulum' => 'Waka Kurikulum',
        'waka_kesiswaan' => 'Waka Kesiswaan',
        'waka_sarpras' => 'Waka Sarpras',
        'waka_humas' => 'Waka Humas',
        'staf_tu' => 'Staf Tata Usaha',
        'bendahara' => 'Bendahara',
        'bk' => 'Guru BK',
        'wali_kelas' => 'Wali Kelas',
        'petugas_uks' => 'Petugas UKS',
        'operator' => 'Operator',
        'merchant' => 'Mitra Kantin',
        'alumni' => 'Alumni',
    ];

    public static function of(?string $role): string
    {
        $role = (string) $role;

        return match (true) {
            in_array($role, StudentDataAccess::STUDENT_ROLES, true) => 'Siswa',
            in_array($role, StudentDataAccess::PARENT_ROLES, true) => 'Wali Murid',
            in_array($role, StudentDataAccess::TEACHER_ROLES, true) => 'Guru',
            isset(self::LABELS[$role]) => self::LABELS[$role],
            // A role added later still reads as words, not as a snake_case key.
            default => Str::headline($role),
        };
    }
}
