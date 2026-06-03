<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class UksPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if (in_array($user->role, ['superadmin', 'admin'])) {
            return true;
        }

        return null;
    }

    /**
     * Menentukan apakah user dapat mengelola data UKS utama (Visits & Medicines)
     */
    public function manageUks(User $user): bool
    {
        return in_array($user->role, ['guru', 'bk', 'staf_tu', 'waka_kesiswaan']);
    }

    /**
     * Menentukan apakah orang tua/wali murid dapat melihat riwayat medis anak mereka sendiri
     */
    public function viewStudentHistory(User $user, Student $student): bool
    {
        // 1. Guru, BK, TU, Waka boleh lihat semua riwayat murid
        if (in_array($user->role, ['guru', 'bk', 'staf_tu', 'waka_kesiswaan'])) {
            return true;
        }

        // 2. Orang Tua hanya boleh melihat data anak kandungnya sendiri
        if ($user->role === 'parent') {
            return $student->parent_id === $user->id;
        }

        return false;
    }
}
