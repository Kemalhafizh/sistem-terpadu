<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    /**
     * Determine whether the user can view any models.
     * Staff roles can view the full student list.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            'admin', 'superadmin', 'kepala_sekolah',
            'waka_kurikulum', 'waka_kesiswaan', 'staf_tu', 'guru', 'bk',
        ]);
    }

    /**
     * Determine whether the user can view a specific student.
     * Students can only view their own data.
     * Parents can only view their own children.
     */
    public function view(User $user, Student $student): bool
    {
        if ($user->role === 'student') {
            return $student->user_id === $user->id;
        }

        if ($user->role === 'parent') {
            return $student->parent_id === $user->id;
        }

        // All staff roles can view any student
        return true;
    }

    /**
     * Determine whether the user can create students.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [
            'admin', 'superadmin', 'waka_kurikulum', 'staf_tu',
        ]);
    }

    /**
     * Determine whether the user can update the student.
     */
    public function update(User $user, Student $student): bool
    {
        return in_array($user->role, [
            'admin', 'superadmin', 'waka_kurikulum', 'staf_tu',
        ]);
    }

    /**
     * Determine whether the user can delete the student.
     */
    public function delete(User $user, Student $student): bool
    {
        return in_array($user->role, ['admin', 'superadmin']);
    }
}
