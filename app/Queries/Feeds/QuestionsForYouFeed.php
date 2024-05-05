<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final readonly class QuestionsForYouFeed
{
    /**
     * Create a new instance ForYou feed.
     */
    public function __construct(
        private User $user,
    ) {
    }

    /**
     * Get the query builder for the feed.
     *
     * @return Builder<Question>
     */
    public function builder(): Builder
    {
        return Question::query()
            ->whereHas('to', function (Builder $toQuery): void {
                $toQuery
                    ->whereHas('questionsSent.likes', function (Builder $questionsQuery): void {
                        $questionsQuery->where('user_id', $this->user->id);
                    })
                    ->orWhereHas('questionsReceived.likes', function (Builder $questionsQuery): void {
                        $questionsQuery->where('user_id', $this->user->id);
                    });
            })
            ->orderByDesc('updated_at')
            ->whereNotNull('answer')
            ->where('is_reported', false)
            ->where('is_ignored', false);
    }
}
