<?php

namespace App\Policies;

use App\Http\Middleware\RoleMiddleware;
use App\Models\TeachingJournal;
use App\Models\User;

class TeachingJournalPolicy
{
    public function viewAny(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, [
            'admin', 'superadmin', 'kepala_sekolah', 'kepsek', 'waka_kurikulum', 'guru', 'teacher',
        ]);
    }

    public function view(User $user, TeachingJournal $teachingJournal): bool
    {
        if (RoleMiddleware::allowsUser($user, ['admin', 'superadmin', 'kepala_sekolah', 'kepsek', 'waka_kurikulum'])) {
            return true;
        }

        return RoleMiddleware::allowsUser($user, ['guru', 'teacher']) && (int) $teachingJournal->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, ['guru', 'teacher', 'admin', 'superadmin']);
    }

    public function update(User $user, TeachingJournal $teachingJournal): bool
    {
        if ($teachingJournal->status !== 'draft') {
            return false;
        }

        if (RoleMiddleware::allowsUser($user, ['admin', 'superadmin'])) {
            return true;
        }

        return RoleMiddleware::allowsUser($user, ['guru', 'teacher']) && (int) $teachingJournal->user_id === (int) $user->id;
    }

    public function delete(User $user, TeachingJournal $teachingJournal): bool
    {
        if ($teachingJournal->status !== 'draft') {
            return false;
        }

        if (RoleMiddleware::allowsUser($user, ['admin', 'superadmin'])) {
            return true;
        }

        return RoleMiddleware::allowsUser($user, ['guru', 'teacher']) && (int) $teachingJournal->user_id === (int) $user->id;
    }

    public function review(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, [
            'admin', 'superadmin', 'kepala_sekolah', 'kepsek', 'waka_kurikulum',
        ]);
    }

    public function export(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, [
            'admin', 'superadmin', 'kepala_sekolah', 'kepsek', 'waka_kurikulum',
        ]);
    }
}
