<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

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
        return in_array($user->role, [
            'admin', 'superadmin', 'kepala_sekolah', 'waka_humas',
        ]);
    }

    /**
     * Determine whether the user can delete the announcement.
     * Only the creator or admin can delete.
     */
    public function delete(User $user, Announcement $announcement): bool
    {
        if (in_array($user->role, ['admin', 'superadmin'])) {
            return true;
        }

        return $announcement->created_by === $user->id;
    }
}
