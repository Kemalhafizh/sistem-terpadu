<?php

namespace App\Policies;

use App\Http\Middleware\RoleMiddleware;
use App\Models\Student;
use App\Models\User;
use App\Support\Access\StudentDataAccess;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, [
            'admin', 'superadmin', 'kepala_sekolah', 'kepsek',
            'waka_kurikulum', 'waka_kesiswaan', 'staf_tu', 'guru', 'teacher', 'bk', 'wali_kelas',
        ]);
    }

    public function view(User $user, Student $student): bool
    {
        $roles = $user->effectiveRoles();

        if (array_intersect($roles, StudentDataAccess::STUDENT_ROLES) !== []) {
            return (int) $student->user_id === (int) $user->id;
        }

        if (array_intersect($roles, StudentDataAccess::PARENT_ROLES) !== []) {
            return (int) $student->parent_id === (int) $user->id;
        }

        if (array_intersect($roles, Student::SCHOOL_WIDE_ROLES) !== []) {
            return true;
        }

        if (array_intersect($roles, [...StudentDataAccess::TEACHER_ROLES, 'wali_kelas']) !== []) {
            return $student->isVisibleTo($user);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, [
            'admin', 'superadmin', 'waka_kurikulum', 'staf_tu',
        ]);
    }

    public function update(User $user, Student $student): bool
    {
        return RoleMiddleware::allowsUser($user, [
            'admin', 'superadmin', 'waka_kurikulum', 'staf_tu',
        ]);
    }

    public function delete(User $user, Student $student): bool
    {
        return RoleMiddleware::allowsUser($user, ['admin', 'superadmin']);
    }
}
