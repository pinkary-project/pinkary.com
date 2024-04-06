<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Question;
use App\Models\User;
use App\Notifications\QuestionAnswered;
use App\Notifications\QuestionCreated;
use App\Notifications\UserMentioned;

final readonly class QuestionObserver
{
    /**
     * Handle the question "created" event.
     */
    public function created(Question $question): void
    {
        $user = type(User::find($question->to_id))->as(User::class);

        $user->notify(new QuestionCreated($question));

        preg_match_all("/\@(\w+)/", $question->content, $matches);

        collect(array_unique($matches[1]))->each(function (string $username) use ($question) {
            if (! $user = User::whereUsername($username)->first()) {
                return;
            }

            $user->notify(new UserMentioned($question, $question->from));
        });
    }

    /**
     * Handle the question "updated" event.
     */
    public function updated(Question $question): void
    {
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

        preg_match_all("/\@(\w+)/", $question->answer, $matches);

        collect(array_unique($matches[1]))->each(function (string $username) use ($question) {
            if (! $user = User::whereUsername($username)->first()) {
                return;
            }

            $user->notify(new UserMentioned($question, $question->to));
        });
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
