<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkflowRequest;

class WorkflowPolicy
{
    /**
     * Determine whether the user can view the workflow request details.
     */
    public function view(User $user, WorkflowRequest $request): bool
    {
        // Requester can view their own requests
        if ($user->id === $request->requester_id) {
            return true;
        }

        // Approvers assigned to any step in the workflow can view it
        return $request->approvals()->where('role', $user->role)->exists();
    }
}
