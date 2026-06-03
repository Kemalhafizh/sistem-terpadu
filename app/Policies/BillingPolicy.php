<?php

namespace App\Policies;

use App\Models\Billing;
use App\Models\User;

class BillingPolicy
{
    /**
     * Determine whether the user can view any billings.
     * Admin/TU see all; students/parents see only their own.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            'admin', 'superadmin', 'kepala_sekolah', 'staf_tu',
        ]);
    }

    /**
     * Determine whether the user can view a specific billing.
     * Students can only view their own billings.
     * Parents can only view billings for their children.
     */
    public function view(User $user, Billing $billing): bool
    {
        if ($user->role === 'student') {
            $student = $user->student;

            return $student && $billing->student_id === $student->id;
        }

        if ($user->role === 'parent') {
            return $billing->student && $billing->student->parent_id === $user->id;
        }

        return in_array($user->role, [
            'admin', 'superadmin', 'kepala_sekolah', 'staf_tu',
        ]);
    }

    /**
     * Determine whether the user can create billings.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'superadmin', 'staf_tu']);
    }

    /**
     * Determine whether the user can update the billing.
     */
    public function update(User $user, Billing $billing): bool
    {
        return in_array($user->role, ['admin', 'superadmin', 'staf_tu']);
    }

    /**
     * Determine whether the user can delete the billing.
     */
    public function delete(User $user, Billing $billing): bool
    {
        return in_array($user->role, ['admin', 'superadmin']);
    }
}
