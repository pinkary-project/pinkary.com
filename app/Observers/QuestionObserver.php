<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Question;
use App\Models\User;
use App\Notifications\QuestionCreated;

final readonly class QuestionObserver
{
    /**
     * Handle the question "created" event.
     */
    public function created(Question $question): void
    {
        // If the content is empty, it's an update
        // shared by the user.
        if (empty($question->content)) {
            return;
        }

        $user = User::find($question->to_id);

        assert($user instanceof User);

        $user->notify(new QuestionCreated($question));
    }

    /**
     * Handle the question "updated" event.
     */
    public function updated(Question $question): void
    {
        if ($question->is_reported || $question->answer !== null) {
            $question->to->notifications->where('data.question_id', $question->id)->each->delete();
        }
    }

    /**
     * Handle the question "deleted" event.
     */
    public function deleted(Question $question): void
    {
        $question->to->notifications->where('data.question_id', $question->id)->each->delete();
    }
}
