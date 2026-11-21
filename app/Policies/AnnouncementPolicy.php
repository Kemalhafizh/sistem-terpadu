<?php

namespace App\Policies;

use App\Http\Middleware\RoleMiddleware;
use App\Models\Announcement;
use App\Models\User;
use App\Support\Access\Roles;

class AnnouncementPolicy
{
    /**
     * Determine whether the user can view any announcements.
     * All authenticated users can view announcements (filtered in controller).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create announcements.
     */
    public function create(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, [
            'admin', 'superadmin', ...Roles::PRINCIPAL, 'waka_humas',
        ]);
    }

    /**
     * Determine whether the user can update the announcement.
     */
    public function update(User $user, Announcement $announcement): bool
    {
        if (RoleMiddleware::allowsUser($user, ['admin', 'superadmin'])) {
            return true;
        }

        return $announcement->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the announcement.
     * Only the creator or admin can delete.
     */
    public function delete(User $user, Announcement $announcement): bool
    {
        if (RoleMiddleware::allowsUser($user, ['admin', 'superadmin'])) {
            return true;
        }

        return $announcement->created_by === $user->id;
    }
}
