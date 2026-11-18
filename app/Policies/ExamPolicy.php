<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\User;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;

class ExamPolicy
{
    /**
     * Determine whether the user can view any exams.
     */
    public function viewAny(User $user): bool
    {
        return array_intersect($user->effectiveRoles(), [
            ...StudentDataAccess::TEACHER_ROLES,
            ...StudentDataAccess::STUDENT_ROLES,
            ...Roles::PRINCIPAL,
            'waka_kurikulum',
            'admin',
            'superadmin',
        ]) !== [];
    }

    /**
     * Determine whether the user can view the exam details.
     */
    public function view(User $user, Exam $exam): bool
    {
        $roles = $user->effectiveRoles();

        if (array_intersect($roles, [...Roles::PRINCIPAL, 'waka_kurikulum', 'admin', 'superadmin']) !== []) {
            return true;
        }

        if (array_intersect($roles, StudentDataAccess::TEACHER_ROLES) !== []) {
            return $exam->created_by === $user->id;
        }

        if (array_intersect($roles, StudentDataAccess::STUDENT_ROLES) !== []) {
            // Null classroom means open to all, or classroom matches student classroom
            return is_null($exam->classroom_id) || $exam->classroom_id === $user->student?->classroom_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create exams.
     */
    public function create(User $user): bool
    {
        return array_intersect($user->effectiveRoles(), [
            ...StudentDataAccess::TEACHER_ROLES,
            'waka_kurikulum',
            'admin',
            'superadmin',
        ]) !== [];
    }

    /**
     * Determine whether the user can update the exam.
     */
    public function update(User $user, Exam $exam): bool
    {
        $roles = $user->effectiveRoles();

        if (array_intersect($roles, ['waka_kurikulum', 'admin', 'superadmin']) !== []) {
            return true;
        }

        return array_intersect($roles, StudentDataAccess::TEACHER_ROLES) !== []
            && $exam->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the exam.
     */
    public function delete(User $user, Exam $exam): bool
    {
        $roles = $user->effectiveRoles();

        if (array_intersect($roles, ['waka_kurikulum', 'admin', 'superadmin']) !== []) {
            return true;
        }

        return array_intersect($roles, StudentDataAccess::TEACHER_ROLES) !== []
            && $exam->created_by === $user->id;
    }

    /**
     * Determine whether the user can monitor the exam in real-time.
     */
    public function monitor(User $user, Exam $exam): bool
    {
        $roles = $user->effectiveRoles();

        if (array_intersect($roles, [...Roles::PRINCIPAL, 'waka_kurikulum', 'admin', 'superadmin']) !== []) {
            return true;
        }

        return array_intersect($roles, StudentDataAccess::TEACHER_ROLES) !== []
            && $exam->created_by === $user->id;
    }

    /**
     * Determine whether the student can take/participate in the exam.
     */
    public function take(User $user, Exam $exam): bool
    {
        if (array_intersect($user->effectiveRoles(), StudentDataAccess::STUDENT_ROLES) === []) {
            return false;
        }

        $student = $user->student;
        if (! $student) {
            return false;
        }

        // Classroom matching logic
        return is_null($exam->classroom_id) || $exam->classroom_id === $student->classroom_id;
    }
}
