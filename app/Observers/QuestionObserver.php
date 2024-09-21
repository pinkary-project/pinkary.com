<?php

declare(strict_types=1);

namespace App\Observers;

use App\EventActions\UpdateQuestionHashtags;
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
        if ($question->isSharedUpdate()) {
            if ($question->parent_id !== null) {
                $question->loadMissing('parent.to');
                if ($question->parent?->to_id !== $question->to_id) {
                    $question->parent?->to->notify(new QuestionCreated($question));
                }
            }

            $question->mentions()->each->notify(new UserMentioned($question));
        } else {
            $question->loadMissing('to');
            $question->to->notify(new QuestionCreated($question));
        }

        (new UpdateQuestionHashtags($question))->handle();
    }

    /**
     * Handle the question "updated" event.
     */
    public function updated(Question $question): void
    {
        $question->loadMissing('from', 'to');

        if ($question->is_ignored || $question->is_reported) {
            $this->deleted($question);

            return;
        }

        if ($question->answer !== null) {
            $question->to->notifications()->whereJsonContains('data->question_id', $question->id)->delete();
        }

        if ($question->isDirty(['answer', 'content'])) {
            (new UpdateQuestionHashtags($question))->handle();
        }

        if ($question->isDirty('answer') === false) {
            return;
        }

        if ($question->from->id === $question->to->id) {
            return;
        }

        $question->from->notify(new QuestionAnswered($question));
        $question->mentions()->each->notify(new UserMentioned($question));
    }

    /**
     * Handle the question "deleted" event.
     */
    public function deleted(Question $question): void
    {
        $question->to->notifications()->whereJsonContains('data->question_id', $question->id)->delete();
        $question->from->notifications()->whereJsonContains('data->question_id', $question->id)->delete();

        $question->mentions()->each(function (User $user) use ($question): void {
            $user->notifications()->whereJsonContains('data->question_id', $question->id)->delete();
        });

        $question->loadMissing(['children', 'descendants']);

        $question->children->each->delete();

        $question->descendants->each->delete();

        $question->hashtags()->detach();
    }
}
