<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\User;

class GradePolicy
{
    /**
     * Determine whether the user can view any grades.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            'admin', 'superadmin', 'kepala_sekolah', 'waka_kurikulum', 'guru', 'bk',
        ]);
    }

    /**
     * Determine whether the user can view a specific grade.
     * Students see only their own. Parents see their children's.
     */
    public function view(User $user, Grade $grade): bool
    {
        if ($user->role === 'student') {
            $student = $user->student;

            return $student && $grade->student_id === $student->id;
        }

        if ($user->role === 'parent') {
            return $grade->student && $grade->student->parent_id === $user->id;
        }

        return true;
    }

    /**
     * Determine whether the user can create grades.
     * Only teachers (guru) and admin can input grades.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'superadmin', 'guru']);
    }

    /**
     * Determine whether the user can update the grade.
     */
    public function update(User $user, Grade $grade): bool
    {
        // Teachers can only update grades they recorded
        if ($user->role === 'guru') {
            return $grade->recorded_by === $user->id;
        }

        return in_array($user->role, ['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can delete the grade.
     */
    public function delete(User $user, Grade $grade): bool
    {
        return in_array($user->role, ['admin', 'superadmin']);
    }
}
