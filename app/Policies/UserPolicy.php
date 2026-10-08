<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserQuestionPreference;
use App\Models\User;

final readonly class UserPolicy
{
    /**
     * Determine whether the visitor can ask the user a question.
     */
    public function askQuestion(?User $user, User $target): bool
    {
        return match ($target->question_preference) {
            UserQuestionPreference::Everyone => true,
            UserQuestionPreference::Following => $user instanceof User && $target->following()->whereKey($user->id)->exists(),
            UserQuestionPreference::NoOne => false,
        };
    }

    /**
     * Determine whether the user can follow the user.
     */
    public function follow(User $user, User $target): bool
    {
        return $user->id !== $target->id;
    }

    /**
     * Determine whether the user can unfollow the user.
     */
    public function unfollow(User $user, User $target): bool
    {
        return $user->id !== $target->id;
    }
}
