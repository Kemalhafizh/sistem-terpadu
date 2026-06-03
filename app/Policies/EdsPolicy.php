<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EdsPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any EDS periods.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['kepala_sekolah', 'waka_kurikulum', 'admin', 'superadmin']);
    }

    /**
     * Determine whether the user can manage EDS periods (create, update status).
     */
    public function manage(User $user): bool
    {
        return in_array($user->role, ['waka_kurikulum', 'admin', 'superadmin']);
    }

    /**
     * Determine whether the user can assess/evaluate standard indicators.
     */
    public function assess(User $user): bool
    {
        return in_array($user->role, ['waka_kurikulum', 'admin', 'superadmin']);
    }
}
