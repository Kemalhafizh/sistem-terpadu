<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\User;

class ExamPolicy
{
    /**
     * Determine whether the user can view any exams.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['guru', 'siswa', 'student', 'waka_kurikulum', 'kepala_sekolah', 'admin', 'superadmin']);
    }

    /**
     * Determine whether the user can view the exam details.
     */
    public function view(User $user, Exam $exam): bool
    {
        if (in_array($user->role, ['waka_kurikulum', 'kepala_sekolah', 'admin', 'superadmin'])) {
            return true;
        }

        if ($user->role === 'guru') {
            return $exam->created_by === $user->id;
        }

        if (in_array($user->role, ['siswa', 'student'])) {
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
        return in_array($user->role, ['guru', 'waka_kurikulum', 'admin', 'superadmin']);
    }

    /**
     * Determine whether the user can update the exam.
     */
    public function update(User $user, Exam $exam): bool
    {
        if (in_array($user->role, ['waka_kurikulum', 'admin', 'superadmin'])) {
            return true;
        }

        return $user->role === 'guru' && $exam->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the exam.
     */
    public function delete(User $user, Exam $exam): bool
    {
        if (in_array($user->role, ['waka_kurikulum', 'admin', 'superadmin'])) {
            return true;
        }

        return $user->role === 'guru' && $exam->created_by === $user->id;
    }

    /**
     * Determine whether the user can monitor the exam in real-time.
     */
    public function monitor(User $user, Exam $exam): bool
    {
        if (in_array($user->role, ['waka_kurikulum', 'kepala_sekolah', 'admin', 'superadmin'])) {
            return true;
        }

        return $user->role === 'guru' && $exam->created_by === $user->id;
    }

    /**
     * Determine whether the student can take/participate in the exam.
     */
    public function take(User $user, Exam $exam): bool
    {
        if (!in_array($user->role, ['siswa', 'student'])) {
            return false;
        }

        $student = $user->student;
        if (!$student) {
            return false;
        }

        // Classroom matching logic
        return is_null($exam->classroom_id) || $exam->classroom_id === $student->classroom_id;
    }
}
