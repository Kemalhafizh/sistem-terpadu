<?php

namespace App\Policies;

use App\Http\Middleware\RoleMiddleware;
use App\Models\Grade;
use App\Models\User;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;

class GradePolicy
{
    /**
     * Determine whether the user can view any grades.
     */
    public function viewAny(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, [
            'admin', 'superadmin', ...Roles::PRINCIPAL, 'waka_kurikulum', ...StudentDataAccess::TEACHER_ROLES, 'bk',
        ]);
    }

    /**
     * Determine whether the user can view a specific grade.
     * Students see only their own. Parents see their children's.
     */
    public function view(User $user, Grade $grade): bool
    {
        if ($user->hasRole('student')) {
            $student = $user->student;

            return $student && $grade->student_id === $student->id;
        }

        if ($user->hasRole('parent')) {
            return $grade->student && $grade->student->parent_id === $user->id;
        }

        return RoleMiddleware::allowsUser($user, [
            'admin', 'superadmin', ...Roles::PRINCIPAL, 'waka_kurikulum', ...StudentDataAccess::TEACHER_ROLES, 'bk',
        ]);
    }

    /**
     * Determine whether the user can create grades.
     * Only teachers (guru) and admin can input grades.
     */
    public function create(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, ['admin', 'superadmin', ...StudentDataAccess::TEACHER_ROLES]);
    }

    /**
     * Determine whether the user can update the grade.
     */
    public function update(User $user, Grade $grade): bool
    {
        if (RoleMiddleware::allowsUser($user, ['admin', 'superadmin'])) {
            return true;
        }

        // Teachers can only update grades they recorded
        if ($user->hasRole('guru')) {
            return $grade->recorded_by === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the grade.
     */
    public function delete(User $user, Grade $grade): bool
    {
        return RoleMiddleware::allowsUser($user, ['admin', 'superadmin']);
    }
}
