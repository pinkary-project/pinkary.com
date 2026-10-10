<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Repost;
use App\Notifications\QuestionReposted;
use Illuminate\Notifications\DatabaseNotification;

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
        DatabaseNotification::query()
            ->where('type', QuestionReposted::class)
            ->whereJsonContains('data->repost_id', $repost->id)
            ->delete();
    }
}
