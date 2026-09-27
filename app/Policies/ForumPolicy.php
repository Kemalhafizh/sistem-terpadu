<?php

namespace App\Policies;

use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\User;

class ForumPolicy
{
    /**
     * Determine whether the user can view any threads.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the thread.
     */
    public function view(User $user, ForumThread $thread): bool
    {
        // Admins can see everything
        if ($user->isAdmin() || $user->role === 'guru' || $user->role === 'employee') {
            return true;
        }

        // Parent authorization: check child's classroom
        if ($user->role === 'parent') {
            if ($thread->scope === 'school') {
                return true;
            }

            $childClassrooms = $user->children()->pluck('classroom_id')->toArray();

            if ($thread->classroom_id && in_array($thread->classroom_id, $childClassrooms)) {
                return true;
            }

            if ($thread->elearningClass && in_array($thread->elearningClass->classroom_id, $childClassrooms)) {
                return true;
            }

            return false;
        }

        // Student authorization
        if ($user->role === 'student') {
            if ($thread->scope === 'school') {
                return true;
            }

            $student = $user->student;
            if (! $student) {
                return false;
            }

            if ($thread->classroom_id && $student->classroom_id === $thread->classroom_id) {
                return true;
            }

            if ($thread->elearningClass && $student->classroom_id === $thread->elearningClass->classroom_id) {
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * Determine whether the user can create threads.
     */
    public function createThread(User $user): bool
    {
        return $user->role !== 'parent';
    }

    /**
     * Determine whether the user can reply to the thread.
     */
    public function reply(User $user, ForumThread $thread): bool
    {
        if ($user->role === 'parent') {
            return false;
        }

        if ($thread->is_locked && ! $user->isAdmin() && $user->role !== 'guru') {
            return false;
        }

        return $this->view($user, $thread);
    }

    /**
     * Determine whether the user can react to the thread or reply.
     */
    public function react(User $user, ForumThread $thread): bool
    {
        if ($user->role === 'parent') {
            return false;
        }

        return $this->view($user, $thread);
    }

    /**
     * Determine whether the user can moderate (pin, lock, delete) the thread.
     */
    public function moderate(User $user, ForumThread $thread): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->role === 'guru') {
            // Is author of the thread
            if ($thread->user_id === $user->id) {
                return true;
            }

            // Is teacher of the classroom/elearning class
            if ($thread->classroom && $thread->classroom->teacher_id === $user->id) {
                return true;
            }

            if ($thread->elearningClass && $thread->elearningClass->teacher_id === $user->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can update the thread.
     */
    public function updateThread(User $user, ForumThread $thread): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($thread->is_locked) {
            return false;
        }

        return $thread->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the thread.
     */
    public function deleteThread(User $user, ForumThread $thread): bool
    {
        if ($user->isAdmin() || $user->role === 'waka_kurikulum' || $user->role === 'waka_kesiswaan') {
            return true;
        }

        return $thread->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the reply.
     */
    public function updateReply(User $user, ForumReply $reply): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($reply->thread->is_locked) {
            return false;
        }

        return $reply->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the reply.
     */
    public function deleteReply(User $user, ForumReply $reply): bool
    {
        if ($user->isAdmin() || $user->role === 'waka_kurikulum' || $user->role === 'waka_kesiswaan') {
            return true;
        }

        return $reply->user_id === $user->id;
    }

    /**
     * Determine whether the user can mark a reply as best answer.
     */
    public function markBestAnswer(User $user, ForumThread $thread): bool
    {
        return $this->moderate($user, $thread);
    }
}
