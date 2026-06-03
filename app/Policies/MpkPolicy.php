<?php

namespace App\Policies;

use App\Models\User;

class MpkPolicy
{
    public function viewDashboard(User $user): bool
    {
        return in_array($user->role, ['guru', 'waka_kesiswaan', 'kepala_sekolah', 'admin', 'superadmin']);
    }

    public function manageAudit(User $user): bool
    {
        return in_array($user->role, ['waka_kesiswaan', 'kepala_sekolah', 'admin', 'superadmin']);
    }

    public function reviewLpj(User $user): bool
    {
        return in_array($user->role, ['waka_kesiswaan', 'kepala_sekolah', 'admin', 'superadmin']);
    }

    public function resolveAspiration(User $user): bool
    {
        return in_array($user->role, ['waka_kesiswaan', 'kepala_sekolah', 'admin', 'superadmin']);
    }
}
