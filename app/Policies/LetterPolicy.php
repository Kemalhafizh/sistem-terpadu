<?php

namespace App\Policies;

use App\Models\Letter;
use App\Models\User;

class LetterPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if (in_array($user->role, ['superadmin', 'admin'])) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['staf_tu', 'kepala_sekolah']);
    }

    public function view(User $user, Letter $letter): bool
    {
        return in_array($user->role, ['staf_tu', 'kepala_sekolah']) ||
               ($user->role === 'guru' && $letter->employee_id === $user->employee?->id);
    }

    public function create(User $user): bool
    {
        return $user->role === 'staf_tu';
    }

    public function update(User $user, Letter $letter): bool
    {
        // Hanya draf yang dapat diedit oleh TU pembuat atau TU mana saja
        return $user->role === 'staf_tu' && $letter->status === 'draft';
    }

    public function delete(User $user, Letter $letter): bool
    {
        return $user->role === 'staf_tu' && $letter->status === 'draft';
    }

    public function issue(User $user): bool
    {
        return $user->role === 'staf_tu';
    }
}
