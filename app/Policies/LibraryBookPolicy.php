<?php

namespace App\Policies;

use App\Models\LibraryBook;
use App\Models\User;

class LibraryBookPolicy
{
    /**
     * Determine whether the user can view any library books.
     * All authenticated users can browse the catalog.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a specific book.
     */
    public function view(User $user, LibraryBook $libraryBook): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create library books.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'superadmin', 'guru', 'staf_tu']);
    }

    /**
     * Determine whether the user can update the book.
     */
    public function update(User $user, LibraryBook $libraryBook): bool
    {
        if (in_array($user->role, ['admin', 'superadmin'])) {
            return true;
        }

        // Creator can update their own uploads
        return $libraryBook->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the book.
     * Only the creator or admin can delete.
     */
    public function delete(User $user, LibraryBook $libraryBook): bool
    {
        if (in_array($user->role, ['admin', 'superadmin'])) {
            return true;
        }

        return $libraryBook->created_by === $user->id;
    }
}
