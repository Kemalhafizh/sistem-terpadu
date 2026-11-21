<?php

namespace App\Policies;

use App\Http\Middleware\RoleMiddleware;
use App\Models\Student;
use App\Models\User;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;

class UksPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if (RoleMiddleware::allowsUser($user, ['superadmin', 'admin'])) {
            return true;
        }

        return null;
    }

    /**
     * Menentukan apakah user dapat mengelola data UKS utama (Visits & Medicines)
     */
    public function manageUks(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, [
            ...StudentDataAccess::TEACHER_ROLES,
            'bk',
            'petugas_uks',
            'staf_tu',
            'waka_kesiswaan',
        ]);
    }

    /**
     * Menentukan apakah orang tua/wali murid dapat melihat riwayat medis anak mereka sendiri
     */
    public function viewStudentHistory(User $user, Student $student): bool
    {
        // 1. Guru, BK, Petugas UKS, TU, Waka Kesiswaan, Kepala Sekolah boleh lihat riwayat medis murid
        if (RoleMiddleware::allowsUser($user, [
            ...StudentDataAccess::TEACHER_ROLES,
            'wali_kelas',
            'bk',
            'petugas_uks',
            'staf_tu',
            'waka_kesiswaan',
            ...Roles::PRINCIPAL,
        ])) {
            return true;
        }

        // 2. Siswa hanya boleh melihat riwayat miliknya sendiri
        if (RoleMiddleware::allowsUser($user, StudentDataAccess::STUDENT_ROLES)) {
            return $student->user_id === $user->id;
        }

        // 3. Orang Tua hanya boleh melihat data anak kandungnya sendiri
        if (RoleMiddleware::allowsUser($user, StudentDataAccess::PARENT_ROLES)) {
            return $student->parent_id === $user->id;
        }

        return false;
    }
}
