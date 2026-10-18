<?php

declare(strict_types=1);

namespace App\Observers;

use App\Actions\Questions\UpdateQuestionHashtags;
use App\Models\Question;
use App\Models\User;
use App\Notifications\QuestionAnswered;
use App\Notifications\QuestionCreated;
use App\Notifications\UserMentioned;

final readonly class QuestionObserver
{
    /**
     * Create a new observer instance.
     */
    public function __construct(
        private UpdateQuestionHashtags $updateQuestionHashtags,
    ) {}

    /**
     * Handle the question "created" event.
     */
    public function created(Question $question): void
    {
        if (! $question->requires_approval) {
            $this->notifyCreated($question);
        }

        $this->updateQuestionHashtags->handle($question);
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

        if ($question->wasChanged('requires_approval') && ! $question->requires_approval) {
            if ($question->isSharedUpdate()) {
                $this->notifyCreated($question);
            } elseif ($question->answer !== null) {
                $this->notifyAnswered($question);
            }
        }

        if ($question->answer !== null) {
            $question->to->notifications()->whereJsonContains('data->question_id', $question->id)->delete();
        }

        if ($question->isDirty(['answer', 'content'])) {
            $this->updateQuestionHashtags->handle($question);
        }

        if ($question->isDirty('answer') === false) {
            return;
        }

        if ($question->requires_approval) {
            return;
        }

        $this->notifyAnswered($question);
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

        $question->loadMissing(['children', 'descendants', 'reposts']);

        $question->reposts->each->delete();

        $question->children->each->delete();

        $question->descendants->each->delete();

        $question->hashtags()->detach();
    }

    /**
     * Notify the question author and mentioned users that the question was answered.
     */
    private function notifyAnswered(Question $question): void
    {
        if ($question->from->id === $question->to->id) {
            return;
        }

        $question->from->notify(new QuestionAnswered($question));
        $question->mentions()->each->notify(new UserMentioned($question));
    }

    /**
     * Notify the recipient and mentioned users that the question was created.
     */
    private function notifyCreated(Question $question): void
    {
        if ($question->isSharedUpdate()) {
            if ($question->parent_id !== null) {
                $question->loadMissing('parent.to');
                if ($question->parent?->to_id !== $question->to_id) {
                    $question->parent?->to->notify(new QuestionCreated($question));
                }
            }

            $question->mentions()->each->notify(new UserMentioned($question));

            return;
        }

        $question->loadMissing('to');
        $question->to->notify(new QuestionCreated($question));
    }
}
