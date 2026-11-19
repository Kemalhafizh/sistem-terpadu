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
        if ((int) $user->id === (int) $request->requester_id) {
            return true;
        }

        return $request->approvals()
            ->where(function ($query) use ($user) {
                $query->whereIn('role', $user->effectiveRoles())
                    ->orWhere('delegated_to_id', $user->id);
            })
            ->exists();
    }
}
