<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Question;
use App\Models\User;
use App\Notifications\QuestionAnswered;
use App\Notifications\QuestionCreated;

final readonly class QuestionObserver
{
    /**
     * Handle the question "created" event.
     */
    public function created(Question $question): void
    {
        $user = type(User::find($question->to_id))->as(User::class);

        $user->notify(new QuestionCreated($question));
    }

    /**
     * Handle the question "updated" event.
     */
    public function updated(Question $question): void
    {
        if ($question->is_ignored) {
            $question->to->notifications->where('data.question_id', $question->id)->each->delete();
            $question->from->notifications->where('data.question_id', $question->id)->each->delete();

            return;
        }

        if ($question->is_reported || $question->answer !== null) {
            $question->to->notifications->where('data.question_id', $question->id)->each->delete();
        }

        if ($question->isDirty('answer') === false) {
            return;
        }

        if ($question->from->id === $question->to->id) {
            return;
        }

        $question->from->notify(new QuestionAnswered($question));
    }

    /**
     * Handle the question "deleted" event.
     */
    public function deleted(Question $question): void
    {
        $question->to->notifications->where('data.question_id', $question->id)->each->delete();
        $question->from->notifications->where('data.question_id', $question->id)->each->delete();
    }
}
