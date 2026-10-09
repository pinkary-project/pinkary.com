<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Repost;
use App\Notifications\QuestionReposted;

final readonly class RepostObserver
{
    /**
     * Handle the Repost "created" event.
     */
    public function created(Repost $repost): void
    {
        $repost->loadMissing(['question.to', 'user']);

        if ($repost->question->to_id !== $repost->user_id) {
            $repost->question->to->notify(new QuestionReposted($repost));
        }
    }

    /**
     * Handle the Repost "deleted" event.
     */
    public function deleted(Repost $repost): void
    {
        $repost->loadMissing('question.to');

        $repost->question?->to?->notifications()
            ->whereJsonContains('data->repost_id', $repost->id)
            ->delete();
    }
}
