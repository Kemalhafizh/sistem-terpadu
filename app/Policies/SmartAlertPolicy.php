<?php

namespace App\Policies;

use App\Models\SmartAlert;
use App\Models\User;

class SmartAlertPolicy
{
    /**
     * Determine whether the user can view the smart alerts index.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['kepala_sekolah', 'admin', 'superadmin', 'bk', 'waka_kesiswaan', 'waka_kurikulum', 'guru']);
    }

    /**
     * Determine whether the user can acknowledge the alert.
     */
    public function acknowledge(User $user, SmartAlert $alert): bool
    {
        return in_array($user->role, ['kepala_sekolah', 'admin', 'superadmin', 'bk', 'waka_kesiswaan', 'waka_kurikulum']);
    }

    /**
     * Determine whether the user can resolve the alert.
     */
    public function resolve(User $user, SmartAlert $alert): bool
    {
        return in_array($user->role, ['kepala_sekolah', 'admin', 'superadmin', 'bk', 'waka_kesiswaan', 'waka_kurikulum']);
    }
}
