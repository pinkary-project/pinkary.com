<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;

final readonly class AnswerPolicy
{

    /**
     * Determine whether the user can answer the question.
     */
    public function update(User $user, Answer $answer): bool
    {
        return $user->id === $answer->question->to_id;
    }

}
