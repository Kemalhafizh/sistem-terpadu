<?php

namespace App\Policies;

use App\Http\Middleware\RoleMiddleware;
use App\Models\CounselingSession;
use App\Models\Student;
use App\Models\User;
use App\Support\Access\Roles;

class CounselingSessionPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if (RoleMiddleware::allowsUser($user, ['superadmin', 'admin'])) {
            return true;
        }

        return null;
    }

    /**
     * Menentukan apakah user dapat melihat daftar sesi konseling
     */
    public function viewAny(User $user): bool
    {
        // Pihak manajemen sekolah & konselor
        if (RoleMiddleware::allowsUser($user, ['bk', 'waka_kesiswaan', ...Roles::PRINCIPAL])) {
            return true;
        }

        // Siswa dan orang tua memiliki hak akses terbatas
        return $user->hasRole('student') || $user->hasRole('parent');
    }

    /**
     * Menentukan apakah user dapat melihat sesi konseling tertentu
     */
    public function view(User $user, CounselingSession $session): bool
    {
        // 1. Konselor BK, Waka Kesiswaan, Kepala Sekolah
        if (RoleMiddleware::allowsUser($user, ['bk', 'waka_kesiswaan', ...Roles::PRINCIPAL])) {
            return true;
        }

        // 2. Siswa yang bersangkutan
        if ($user->hasRole('student')) {
            $student = $user->student;

            return $student && $session->student_id === $student->id;
        }

        // 3. Orang Tua dari siswa yang bersangkutan
        if ($user->hasRole('parent')) {
            return $session->student && $session->student->parent_id === $user->id;
        }

        return false;
    }

    /**
     * Menentukan apakah user dapat mengajukan sesi konseling
     */
    public function create(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, ['bk']) || $user->hasRole('student') || $user->hasRole('parent');
    }

    /**
     * Menentukan apakah user dapat mengedit/mengubah detail pengajuan sesi konseling
     */
    public function update(User $user, CounselingSession $session): bool
    {
        // Hanya Guru BK yang bisa mengubah
        return RoleMiddleware::allowsUser($user, ['bk']);
    }

    /**
     * Menentukan apakah user dapat membatalkan atau menghapus sesi konseling
     */
    public function delete(User $user, CounselingSession $session): bool
    {
        // Hanya Guru BK yang bisa menghapus
        return RoleMiddleware::allowsUser($user, ['bk']);
    }

    /**
     * Menentukan apakah user dapat mengelola status teknis sesi (schedule, start, complete, cancel)
     */
    public function manage(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, ['bk']);
    }

    /**
     * Menentukan apakah user dapat melihat profil analisis dan riwayat lengkap konseling murid
     */
    public function viewStudentProfile(User $user, Student $student): bool
    {
        // Guru BK, Waka Kesiswaan, Kepala Sekolah boleh melihat profil lengkap bimbingan
        if (RoleMiddleware::allowsUser($user, ['bk', 'waka_kesiswaan', ...Roles::PRINCIPAL])) {
            return true;
        }

        // Siswa boleh melihat miliknya sendiri
        if ($user->hasRole('student')) {
            return $student->user_id === $user->id;
        }

        // Orang tua boleh melihat milik anaknya sendiri
        if ($user->hasRole('parent')) {
            return $student->parent_id === $user->id;
        }

        return false;
    }

    /**
     * Menentukan apakah user dapat mengunduh laporan PDF resmi berita acara BK
     */
    public function downloadReport(User $user, CounselingSession $session): bool
    {
        // Guru BK, Waka Kesiswaan, Kepala Sekolah boleh mengunduh laporan resmi
        if (RoleMiddleware::allowsUser($user, ['bk', 'waka_kesiswaan', ...Roles::PRINCIPAL])) {
            return true;
        }

        // Siswa boleh mengunduh laporan mereka jika tidak dikategorikan super rahasia (opsional, tapi untuk kepatuhan policy)
        if ($user->hasRole('student')) {
            return $session->student_id === $user->student?->id;
        }

        if ($user->hasRole('parent')) {
            return $session->student && $session->student->parent_id === $user->id;
        }

        return false;
    }
}
