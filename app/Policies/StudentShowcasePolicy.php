<?php

namespace App\Policies;

use App\Models\StudentShowcase;
use App\Models\User;

/**
 * Admin/superadmin pass every check through Gate::before (AppServiceProvider)
 * — moderation. Rules that must hold even for them (only students publish,
 * nobody endorses their own work, one endorsement each) live in
 * ShowcaseService.
 */
class StudentShowcasePolicy
{
    /** Staff who may see private entries (e.g. a teacher reviewing work). */
    private const STAFF_ROLES = ['guru', 'bk', 'kepala_sekolah', 'waka_kurikulum', 'waka_kesiswaan', 'waka_humas', 'waka_sarpras', 'staf_tu'];

    public function create(User $user): bool
    {
        return in_array($user->role, StudentShowcase::STUDENT_ROLES, true);
    }

    public function view(User $user, StudentShowcase $showcase): bool
    {
        return $showcase->is_public
            || $showcase->isOwnedBy($user)
            || in_array($user->role, self::STAFF_ROLES, true);
    }

    public function update(User $user, StudentShowcase $showcase): bool
    {
        return $showcase->isOwnedBy($user);
    }

    public function delete(User $user, StudentShowcase $showcase): bool
    {
        return $showcase->isOwnedBy($user);
    }

    /** Anyone who can see a public entry, except its author. */
    public function endorse(User $user, StudentShowcase $showcase): bool
    {
        return $showcase->is_public && ! $showcase->isOwnedBy($user);
    }
}
