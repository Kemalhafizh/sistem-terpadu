<?php

namespace App\Policies;

use App\Http\Middleware\RoleMiddleware;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Everyone logged in can view the helpdesk tickets list
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // Admins, TU staff, and Sarpras staff can view all tickets
        if (RoleMiddleware::allowsUser($user, ['staf_tu', 'waka_sarpras', 'admin', 'superadmin'])) {
            return true;
        }

        // Standard users (students, parents, teachers) can only view their own tickets
        return $user->id === $ticket->reporter_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Everyone logged in can create a support ticket
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        if (RoleMiddleware::allowsUser($user, ['staf_tu', 'waka_sarpras', 'admin', 'superadmin'])) {
            return true;
        }

        // Reporter can edit their own ticket, but only if it's still open
        return $user->id === $ticket->reporter_id && $ticket->status === 'open';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        // Only staff/admin can delete tickets
        return RoleMiddleware::allowsUser($user, ['staf_tu', 'waka_sarpras', 'admin', 'superadmin']);
    }

    /**
     * Determine whether the user can comment on the ticket.
     */
    public function comment(User $user, Ticket $ticket): bool
    {
        if (RoleMiddleware::allowsUser($user, ['staf_tu', 'waka_sarpras', 'admin', 'superadmin'])) {
            return true;
        }

        // Reporters can comment on their own active tickets (not closed)
        return $user->id === $ticket->reporter_id && $ticket->status !== 'closed';
    }

    /**
     * Determine whether the user can view or post internal-only comments.
     */
    public function viewInternalComments(User $user, Ticket $ticket): bool
    {
        // Only staff/admin can view/post internal comments
        return RoleMiddleware::allowsUser($user, ['staf_tu', 'waka_sarpras', 'admin', 'superadmin']);
    }

    /**
     * Determine whether the user can assign a technician/staff to the ticket.
     */
    public function assign(User $user, Ticket $ticket): bool
    {
        return RoleMiddleware::allowsUser($user, ['staf_tu', 'waka_sarpras', 'admin', 'superadmin']);
    }

    /**
     * Determine whether the user can resolve the ticket.
     */
    public function resolve(User $user, Ticket $ticket): bool
    {
        return RoleMiddleware::allowsUser($user, ['staf_tu', 'waka_sarpras', 'admin', 'superadmin']);
    }

    /**
     * Determine whether the user can close the ticket.
     */
    public function close(User $user, Ticket $ticket): bool
    {
        if (RoleMiddleware::allowsUser($user, ['staf_tu', 'waka_sarpras', 'admin', 'superadmin'])) {
            return true;
        }

        // Reporters can close their own tickets once resolved
        return $user->id === $ticket->reporter_id;
    }
}
