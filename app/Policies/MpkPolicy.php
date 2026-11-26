<?php

namespace App\Policies;

use App\Http\Middleware\RoleMiddleware;
use App\Models\User;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;

class MpkPolicy
{
    public function viewDashboard(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, [...StudentDataAccess::TEACHER_ROLES, 'waka_kesiswaan', ...Roles::PRINCIPAL, 'admin', 'superadmin']);
    }

    public function manageAudit(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, ['waka_kesiswaan', ...Roles::PRINCIPAL, 'admin', 'superadmin']);
    }

    public function reviewLpj(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, ['waka_kesiswaan', ...Roles::PRINCIPAL, 'admin', 'superadmin']);
    }

    public function resolveAspiration(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, ['waka_kesiswaan', ...Roles::PRINCIPAL, 'admin', 'superadmin']);
    }
}
