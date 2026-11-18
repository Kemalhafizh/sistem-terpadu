<?php

namespace App\Policies;

use App\Models\ReportCard;
use App\Models\User;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;

class ReportCardPolicy
{
    /**
     * Determine whether the user can view any report cards.
     */
    public function viewAny(User $user): bool
    {
        return array_intersect($user->effectiveRoles(), [
            'admin',
            'superadmin',
            'waka_kurikulum',
            'staf_tu',
            'wali_kelas',
            ...Roles::PRINCIPAL,
            ...StudentDataAccess::TEACHER_ROLES,
        ]) !== [];
    }

    /**
     * Determine whether the user can view a specific report card.
     * Students can only download their own, parents only their children's.
     */
    public function view(User $user, ReportCard $reportCard): bool
    {
        $roles = $user->effectiveRoles();

        if (array_intersect($roles, ['admin', 'superadmin', 'waka_kurikulum', 'staf_tu', ...Roles::PRINCIPAL]) !== []) {
            return true;
        }

        if (array_intersect($roles, [...StudentDataAccess::TEACHER_ROLES, 'wali_kelas']) !== []) {
            return true;
        }

        if (array_intersect($roles, StudentDataAccess::STUDENT_ROLES) !== []) {
            $student = $user->student;

            return $student && $reportCard->student_id === $student->id;
        }

        if (array_intersect($roles, StudentDataAccess::PARENT_ROLES) !== []) {
            $student = $reportCard->student;

            return $student && $student->parent_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can update a report card (e.g. homeroom notes).
     * Academic managers/admin or the classroom's homeroom teacher.
     */
    public function update(User $user, ReportCard $reportCard): bool
    {
        $roles = $user->effectiveRoles();

        if (array_intersect($roles, ['admin', 'superadmin', 'waka_kurikulum', 'staf_tu']) !== []) {
            return true;
        }

        if (array_intersect($roles, [...StudentDataAccess::TEACHER_ROLES, 'wali_kelas']) !== []) {
            return $reportCard->classroom?->teacher_id === $user->id;
        }

        return false;
    }
}
