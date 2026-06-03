<?php

namespace App\Policies;

use App\Models\User;

class CompliancePolicy
{
    /**
     * Determine whether the user can view the compliance dashboard.
     */
    public function viewDashboard(User $user): bool
    {
        return in_array($user->role, ['kepala_sekolah', 'admin', 'superadmin']);
    }

    /**
     * Determine whether the user can manage compliance documents.
     */
    public function manageDocuments(User $user): bool
    {
        return in_array($user->role, ['kepala_sekolah', 'admin', 'superadmin']);
    }

    /**
     * Determine whether the user can manage compliance milestones.
     */
    public function manageMilestones(User $user): bool
    {
        return in_array($user->role, ['kepala_sekolah', 'admin', 'superadmin']);
    }
}
