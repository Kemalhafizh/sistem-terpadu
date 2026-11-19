<?php

namespace App\Policies;

use App\Http\Middleware\RoleMiddleware;
use App\Models\AssetLoan;
use App\Models\User;

class AssetLoanPolicy
{
    public function viewAny(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, [
            'admin', 'superadmin', 'kepala_sekolah', 'kepsek', 'waka_kurikulum',
            'waka_kesiswaan', 'waka_sarpras', 'waka_humas', 'staf_tu', 'guru',
            'teacher', 'wali_kelas', 'bk', 'bendahara', 'pustakawan', 'petugas_uks',
        ]);
    }

    public function view(User $user, AssetLoan $loan): bool
    {
        if (RoleMiddleware::allowsUser($user, ['admin', 'superadmin', 'waka_sarpras', 'kepala_sekolah', 'kepsek', 'staf_tu'])) {
            return true;
        }

        return (int) $loan->borrower_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, ['guru', 'teacher', 'staf_tu', 'admin', 'superadmin', 'waka_sarpras']);
    }

    public function approve(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, ['admin', 'superadmin', 'waka_sarpras']);
    }

    public function return(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, ['admin', 'superadmin', 'waka_sarpras', 'staf_tu']);
    }
}
