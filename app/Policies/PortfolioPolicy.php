<?php

namespace App\Policies;

use App\Models\PortfolioItem;
use App\Models\User;

class PortfolioPolicy
{
    /**
     * Determine whether the user can manage (create, update, delete) their own portfolio items.
     * Note: Once an item is verified, it cannot be updated or deleted by the student.
     */
    public function manage(User $user, PortfolioItem $item): bool
    {
        return $user->role === 'student' &&
               $item->student->user_id === $user->id &&
               ! $item->is_verified;
    }

    /**
     * Determine whether the user can verify portfolio items.
     * Staff roles can review and verify items.
     */
    public function verify(User $user): bool
    {
        return in_array($user->role, ['guru', 'bk', 'admin', 'superadmin', 'kepala_sekolah']);
    }

    /**
     * Determine whether the user can award lencana (badges) to students.
     * Staff roles can award badges.
     */
    public function awardBadge(User $user): bool
    {
        return in_array($user->role, ['guru', 'bk', 'admin', 'superadmin', 'kepala_sekolah']);
    }
}
