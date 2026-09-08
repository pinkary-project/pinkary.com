<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\Question;
use App\Models\User;

final readonly class UpdateQuestionPin
{
    /**
     * Update the question's pinned state, clearing another pinned question when needed.
     */
    public function handle(User $user, Question $question, bool $pinned): void
    {
        if ($pinned) {
            Question::withoutTimestamps(fn () => $user->pinnedQuestion()->update(['pinned' => false]));
        }

        Question::withoutTimestamps(fn () => $question->update(['pinned' => $pinned]));
    }
}
