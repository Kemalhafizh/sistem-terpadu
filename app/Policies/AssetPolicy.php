<?php

namespace App\Policies;

use App\Http\Middleware\RoleMiddleware;
use App\Models\Asset;
use App\Models\User;
use App\Support\Access\Roles;

class AssetPolicy
{
    /**
     * Determine whether the user can view any assets.
     */
    public function viewAny(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, [
            'admin', 'superadmin', ...Roles::PRINCIPAL, 'waka_sarpras',
        ]);
    }

    /**
     * Determine whether the user can view the asset.
     */
    public function view(User $user, Asset $asset): bool
    {
        return RoleMiddleware::allowsUser($user, [
            'admin', 'superadmin', ...Roles::PRINCIPAL, 'waka_sarpras',
        ]);
    }

    /**
     * Determine whether the user can create assets.
     */
    public function create(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, ['admin', 'superadmin', 'waka_sarpras']);
    }

    /**
     * Determine whether the user can update the asset.
     */
    public function update(User $user, Asset $asset): bool
    {
        return RoleMiddleware::allowsUser($user, ['admin', 'superadmin', 'waka_sarpras']);
    }

    /**
     * Determine whether the user can delete the asset.
     */
    public function delete(User $user, Asset $asset): bool
    {
        return RoleMiddleware::allowsUser($user, ['admin', 'superadmin']);
    }
}
