<?php

namespace App\Policies;

use App\Http\Middleware\RoleMiddleware;
use App\Models\LessonPlan;
use App\Models\User;

class CurriculumPolicy
{
    public function viewDashboard(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, ['guru', 'teacher', 'waka_kurikulum', 'kepala_sekolah', 'kepsek', 'admin', 'superadmin']);
    }

    public function manageSyllabus(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, ['guru', 'teacher', 'waka_kurikulum', 'admin', 'superadmin']);
    }

    public function reviewSyllabus(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, ['waka_kurikulum', 'admin', 'superadmin']);
    }

    public function viewLessonPlanList(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, ['guru', 'teacher', 'waka_kurikulum', 'kepala_sekolah', 'kepsek', 'admin', 'superadmin']);
    }

    public function viewLessonPlan(User $user, LessonPlan $rpp): bool
    {
        if (RoleMiddleware::allowsUser($user, ['waka_kurikulum', 'kepala_sekolah', 'kepsek', 'admin', 'superadmin'])) {
            return true;
        }

        return RoleMiddleware::allowsUser($user, ['guru', 'teacher']) && (int) $rpp->user_id === (int) $user->id;
    }

    public function createLessonPlan(User $user): bool
    {
        return array_intersect($user->effectiveRoles(), ['guru', 'teacher']) !== [];
    }

    public function updateLessonPlan(User $user, LessonPlan $rpp): bool
    {
        return array_intersect($user->effectiveRoles(), ['guru', 'teacher']) !== []
            && (int) $rpp->user_id === (int) $user->id
            && in_array($rpp->status, ['draft', 'revision_needed'], true);
    }

    public function deleteLessonPlan(User $user, LessonPlan $rpp): bool
    {
        return array_intersect($user->effectiveRoles(), ['guru', 'teacher']) !== []
            && (int) $rpp->user_id === (int) $user->id
            && $rpp->status === 'draft';
    }

    public function reviewLessonPlan(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, ['waka_kurikulum', 'admin', 'superadmin']);
    }

    public function downloadReport(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, ['waka_kurikulum', 'kepala_sekolah', 'kepsek', 'admin', 'superadmin']);
    }
}
