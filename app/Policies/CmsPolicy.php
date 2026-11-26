<?php

namespace App\Policies;

use App\Http\Middleware\RoleMiddleware;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CmsPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can manage CMS content.
     */
    public function manage(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, ['waka_humas', 'admin', 'superadmin']);
    }
}
