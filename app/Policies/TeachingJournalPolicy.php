<?php

namespace App\Policies;

use App\Models\TeachingJournal;
use App\Models\User;

class TeachingJournalPolicy
{
    /**
     * Determine whether the user can view any teaching journals.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            'admin', 'superadmin', 'kepala_sekolah', 'waka_kurikulum', 'guru',
        ]);
    }

    /**
     * Determine whether the user can view the teaching journal.
     */
    public function view(User $user, TeachingJournal $teachingJournal): bool
    {
        if ($user->role === 'guru') {
            return $teachingJournal->user_id === $user->id;
        }

        return in_array($user->role, [
            'admin', 'superadmin', 'kepala_sekolah', 'waka_kurikulum',
        ]);
    }

    /**
     * Determine whether the user can create teaching journals.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['guru', 'admin', 'superadmin']);
    }

    /**
     * Determine whether the user can update the teaching journal.
     */
    public function update(User $user, TeachingJournal $teachingJournal): bool
    {
        // Only draft journals can be updated
        if ($teachingJournal->status !== 'draft') {
            return false;
        }

        if ($user->role === 'guru') {
            return $teachingJournal->user_id === $user->id;
        }

        return in_array($user->role, ['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can delete the teaching journal.
     */
    public function delete(User $user, TeachingJournal $teachingJournal): bool
    {
        // Only draft journals can be deleted
        if ($teachingJournal->status !== 'draft') {
            return false;
        }

        if ($user->role === 'guru') {
            return $teachingJournal->user_id === $user->id;
        }

        return in_array($user->role, ['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can review/approve the teaching journal.
     */
    public function review(User $user): bool
    {
        return in_array($user->role, [
            'admin', 'superadmin', 'kepala_sekolah', 'waka_kurikulum',
        ]);
    }

    /**
     * Determine whether the user can export the journals to PDF.
     */
    public function export(User $user): bool
    {
        return in_array($user->role, [
            'admin', 'superadmin', 'kepala_sekolah', 'waka_kurikulum',
        ]);
    }
}
