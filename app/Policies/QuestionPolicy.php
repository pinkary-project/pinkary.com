<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Question;
use App\Models\User;

final readonly class QuestionPolicy
{
    /**
     * Determine whether the user can view the question.
     */
    public function view(?User $user, Question $question): bool
    {
        if ($user?->isAdmin() ?? false) {
            return true;
        }

        if ($question->is_ignored || $question->is_reported) {
            return false;
        }

        if ($question->requires_approval && $user?->id !== $question->to_id) {
            return false;
        }

        return $question->answer || $question->to_id === $user?->id;
    }

    /**
     * Determine whether the user can update the question.
     */
    public function update(User $user, Question $question): bool
    {
        return $user->id === $question->to_id;
    }

    /**
     * Determine whether the user can ignore the question.
     */
    public function ignore(User $user, Question $question): bool
    {
        return $user->id === $question->to_id;
    }

    /**
     * Determine whether the user can pin the question.
     */
    public function pin(User $user, Question $question): bool
    {
        return $user->id === $question->to_id
            && $question->answer !== null;
    }

    /**
     * Determine whether the user can view who liked the question.
     */
    public function viewLikes(User $user, Question $question): bool
    {
        return $user->id === $question->to_id;
    }

    /** Determine whether the user can repost the question. */
    public function repost(User $user, Question $question): bool
    {
        return $question->answer !== null
            && ! $question->is_ignored
            && ! $question->is_reported
            && ! $question->requires_approval;
    }

    /** Determine whether the user can approve the question for public display. */
    public function approve(User $user, Question $question): bool
    {
        return $user->isAdmin()
            && $question->requires_approval
            && ! $question->is_ignored
            && ! $question->is_reported;
    }

    /** Determine whether the user can delete the question. */
    public function delete(User $user, Question $question): bool
    {
        return $user->id === $question->to_id;
    }
}
