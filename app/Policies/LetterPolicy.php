<?php

namespace App\Policies;

use App\Models\Letter;
use App\Models\User;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;

class LetterPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($this->hasAnyRole($user, ['superadmin', 'admin'])) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->hasAnyRole($user, ['staf_tu', ...Roles::PRINCIPAL]);
    }

    public function view(User $user, Letter $letter): bool
    {
        return $this->hasAnyRole($user, ['staf_tu', ...Roles::PRINCIPAL])
            || ($this->hasAnyRole($user, StudentDataAccess::TEACHER_ROLES)
                && $letter->employee_id !== null
                && $letter->employee_id === $user->employee?->id);
    }

    public function create(User $user): bool
    {
        return $this->hasAnyRole($user, ['staf_tu']);
    }

    public function update(User $user, Letter $letter): bool
    {
        return $this->hasAnyRole($user, ['staf_tu'])
            && in_array($letter->status, [Letter::STATUS_DRAFT, Letter::STATUS_REVISION], true);
    }

    public function delete(User $user, Letter $letter): bool
    {
        return $this->hasAnyRole($user, ['staf_tu'])
            && in_array($letter->status, [Letter::STATUS_DRAFT, Letter::STATUS_REVISION], true);
    }

    public function submit(User $user, Letter $letter): bool
    {
        return $this->hasAnyRole($user, ['staf_tu'])
            && in_array($letter->status, [Letter::STATUS_DRAFT, Letter::STATUS_REVISION], true);
    }

    public function issue(User $user): bool
    {
        return $this->hasAnyRole($user, ['staf_tu']);
    }

    public function revoke(User $user, Letter $letter): bool
    {
        return $this->hasAnyRole($user, ['staf_tu', ...Roles::PRINCIPAL])
            && $letter->status === Letter::STATUS_ISSUED;
    }

    private function hasAnyRole(User $user, array $roles): bool
    {
        return array_intersect($user->effectiveRoles(), $roles) !== [];
    }
}
