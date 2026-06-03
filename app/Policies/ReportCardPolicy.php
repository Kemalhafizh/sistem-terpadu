<?php

namespace App\Policies;

use App\Models\ReportCard;
use App\Models\User;

class ReportCardPolicy
{
    /**
     * Determine whether the user can view any report cards.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            'admin', 'superadmin', 'kepala_sekolah', 'waka_kurikulum', 'guru',
        ]);
    }

    /**
     * Determine whether the user can view a specific report card.
     * Students can only download their own, parents only their children's.
     */
    public function view(User $user, ReportCard $reportCard): bool
    {
        if ($user->role === 'student') {
            $student = $user->student;

            return $student && $reportCard->student_id === $student->id;
        }

        if ($user->role === 'parent') {
            $student = $reportCard->student;

            return $student && $student->parent_id === $user->id;
        }

        return in_array($user->role, [
            'admin', 'superadmin', 'kepala_sekolah', 'waka_kurikulum', 'guru',
        ]);
    }

    /**
     * Determine whether the user can update a report card (e.g. homeroom notes).
     * Only wali kelas (guru) and admin.
     */
    public function update(User $user, ReportCard $reportCard): bool
    {
        return in_array($user->role, ['admin', 'superadmin', 'guru']);
    }
}
