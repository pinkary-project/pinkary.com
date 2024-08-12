<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Answer;
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
    public function created(Question|Answer $model): void
    {
        if ($model instanceof Question) {
            $this->createdQuestion($model);
        } else {
            $this->createdAnswer($model);
        }

    }

    /**
     * Handle the question "updated" event.
     */
    public function updated(Question|Answer $model): void
    {
        if ($model instanceof Question) {
            $this->updatedQuestion($model);
        } else {
            $this->updatedAnswer($model);
        }

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

        $question->children->each->delete();
    }

    /**
     * Created question.
     */
    private function createdQuestion(Question $question): void
    {
        if ($question->isSharedUpdate()) {
            if ($question->parent_id !== null) {
                $question->loadMissing('parent.to');
                if ($question->parent?->to_id !== $question->to_id) {
                    $question->parent?->to->notify(new QuestionCreated($question));
                }
            }

            $this->notifyMentions($question);
        } else {
            $question->loadMissing('to');
            $question->to->notify(new QuestionCreated($question));
        }
    }

    /**
     * Created answer.
     */
    private function createdAnswer(Answer $answer): void
    {
        $answer->loadMissing('question.to');

        if ($answer->question->is_ignored || $answer->question->is_reported) {
            $this->deleted($answer->question);

            return;
        }

        if ($answer->question->answer !== null) {
            $answer->question->to->notifications()->whereJsonContains('data->question_id', $answer->question_id)->delete();

            $this->notifyMentions($answer->question);
            $answer->question->from->notify(new QuestionAnswered($answer->question));
        }
    }

    /**
     * Updated question.
     */
    private function updatedQuestion(Question $question): void
    {
        if ($question->is_ignored || $question->is_reported) {
            $this->deleted($question);

            return;
        }

        if ($question->isDirty('content') === false) {
            return;
        }

        if ($question->answer !== null || $question->isSharedUpdate()) {
            $question->to->notifications()->whereJsonContains('data->question_id', $question->id)->delete();
        }

        $this->notifyMentions($question);
    }

    /**
     * Updated answer.
     */
    private function updatedAnswer(Answer $answer): void
    {
        if ($answer->isDirty('content') === false) {
            return;
        }

        $answer->loadMissing('question.to');
        $answer->question->to->notifications()->whereJsonContains('data->question_id', $answer->question_id)->delete();

        $this->notifyMentions($answer->question);
    }

    /**
     * Handle mentions for the given model
     */
    private function notifyMentions(Question $question): void
    {
        if ($question->mentions()->isNotEmpty() && $question->mentions()->contains($question->to) === false) {
            $question->mentions()->each->notify(new UserMentioned($question));
        }
    }
}
