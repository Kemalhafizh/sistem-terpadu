<?php

namespace App\Policies;

use App\Models\AssetLoan;
use App\Models\User;

class AssetLoanPolicy
{
    public function viewAny(User $user): bool
    {
        return !in_array($user->role, ['student', 'parent']);
    }

    public function view(User $user, AssetLoan $loan): bool
    {
        if (in_array($user->role, ['admin', 'superadmin', 'waka_sarpras', 'kepala_sekolah', 'staf_tu'])) {
            return true;
        }
        return $loan->borrower_id === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['guru', 'staf_tu', 'admin', 'superadmin', 'waka_sarpras']);
    }

    public function approve(User $user): bool
    {
        return in_array($user->role, ['admin', 'superadmin', 'waka_sarpras']);
    }

    public function return(User $user): bool
    {
        return in_array($user->role, ['admin', 'superadmin', 'waka_sarpras', 'staf_tu']);
    }
}
