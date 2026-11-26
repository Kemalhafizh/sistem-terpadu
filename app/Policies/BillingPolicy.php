<?php

namespace App\Policies;

use App\Http\Middleware\RoleMiddleware;
use App\Models\Billing;
use App\Models\User;
use App\Support\Access\Roles;

class BillingPolicy
{
    /**
     * Determine whether the user can view any billings.
     * Admin/TU see all; students/parents see only their own.
     */
    public function viewAny(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, [
            'admin', 'superadmin', ...Roles::PRINCIPAL, 'staf_tu',
        ]);
    }

    /**
     * Determine whether the user can view a specific billing.
     * Students can only view their own billings.
     * Parents can only view billings for their children.
     */
    public function view(User $user, Billing $billing): bool
    {
        if ($user->hasRole('student')) {
            $student = $user->student;

            return $student && $billing->student_id === $student->id;
        }

        if ($user->hasRole('parent')) {
            return $billing->student && $billing->student->parent_id === $user->id;
        }

        return RoleMiddleware::allowsUser($user, [
            'admin', 'superadmin', ...Roles::PRINCIPAL, 'staf_tu',
        ]);
    }

    /**
     * Determine whether the user can create billings.
     */
    public function create(User $user): bool
    {
        return RoleMiddleware::allowsUser($user, ['admin', 'superadmin', 'staf_tu']);
    }

    /**
     * Determine whether the user can update the billing.
     */
    public function update(User $user, Billing $billing): bool
    {
        return RoleMiddleware::allowsUser($user, ['admin', 'superadmin', 'staf_tu']);
    }

    /**
     * Determine whether the user can delete the billing.
     */
    public function delete(User $user, Billing $billing): bool
    {
        return RoleMiddleware::allowsUser($user, ['admin', 'superadmin']);
    }
}
