<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    /**
     * Determine whether the user can view any assets.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            'admin', 'superadmin', 'kepala_sekolah', 'waka_sarpras',
        ]);
    }

    /**
     * Determine whether the user can view the asset.
     */
    public function view(User $user, Asset $asset): bool
    {
        return in_array($user->role, [
            'admin', 'superadmin', 'kepala_sekolah', 'waka_sarpras',
        ]);
    }

    /**
     * Determine whether the user can create assets.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'superadmin', 'waka_sarpras']);
    }

    /**
     * Determine whether the user can update the asset.
     */
    public function update(User $user, Asset $asset): bool
    {
        return in_array($user->role, ['admin', 'superadmin', 'waka_sarpras']);
    }

    /**
     * Determine whether the user can delete the asset.
     */
    public function delete(User $user, Asset $asset): bool
    {
        return in_array($user->role, ['admin', 'superadmin']);
    }
}
