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
        if ($question->is_reported) {
            return false;
        }

        return ! ($question->answer === null && ! $question->to->is($user));
    }

    /**
     * Determine whether the user can update the question.
     */
    public function update(User $user, Question $question): bool
    {
        return $user->id === $question->to_id;
    }

    /**
     * Determine whether the user can delete the question.
     */
    public function delete(User $user, Question $question): bool
    {
        return $user->id === $question->to_id;
    }

    /**
     * Determine if the user can pin a question.
     */
    public function pin(User $user, Question $question): bool
    {
        return $user->id === $question->to_id && $user->pinnedQuestion()->doesntExist();
    }
}
