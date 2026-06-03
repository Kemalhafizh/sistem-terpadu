<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Syllabus;
use App\Models\LessonPlan;

class CurriculumPolicy
{
    public function viewDashboard(User $user): bool
    {
        return in_array($user->role, ['guru', 'waka_kurikulum', 'kepala_sekolah', 'admin', 'superadmin']);
    }

    public function manageSyllabus(User $user): bool
    {
        return in_array($user->role, ['guru', 'waka_kurikulum', 'admin', 'superadmin']);
    }

    public function reviewSyllabus(User $user): bool
    {
        return in_array($user->role, ['waka_kurikulum', 'admin', 'superadmin']);
    }

    public function viewLessonPlanList(User $user): bool
    {
        return in_array($user->role, ['guru', 'waka_kurikulum', 'kepala_sekolah', 'admin', 'superadmin']);
    }

    public function viewLessonPlan(User $user, LessonPlan $rpp): bool
    {
        if (in_array($user->role, ['waka_kurikulum', 'kepala_sekolah', 'admin', 'superadmin'])) {
            return true;
        }
        return $user->role === 'guru' && $rpp->user_id === $user->id;
    }

    public function createLessonPlan(User $user): bool
    {
        return $user->role === 'guru';
    }

    public function updateLessonPlan(User $user, LessonPlan $rpp): bool
    {
        return $user->role === 'guru' && 
               $rpp->user_id === $user->id && 
               in_array($rpp->status, ['draft', 'revision_needed']);
    }

    public function deleteLessonPlan(User $user, LessonPlan $rpp): bool
    {
        return $user->role === 'guru' && 
               $rpp->user_id === $user->id && 
               $rpp->status === 'draft';
    }

    public function reviewLessonPlan(User $user): bool
    {
        return in_array($user->role, ['waka_kurikulum', 'admin', 'superadmin']);
    }

    public function downloadReport(User $user): bool
    {
        return in_array($user->role, ['waka_kurikulum', 'kepala_sekolah', 'admin', 'superadmin']);
    }
}
