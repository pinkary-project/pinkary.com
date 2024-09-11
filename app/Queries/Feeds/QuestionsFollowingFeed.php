<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final readonly class QuestionsFollowingFeed
{
    /**
     * Create a new instance Following feed.
     */
    public function __construct(
        private User $user,
    ) {}

    /**
     * Get the query builder for the feed.
     *
     * @return Builder<Question>
     */
    public function builder(): Builder
    {
        return Question::query()
            ->whereHas('to', function (Builder $toQuery): void {
                $toQuery->whereIn('id', $this->user->following()->select('users.id'));
            })
            ->orderByDesc('updated_at')
            ->whereNotNull('answer')
            ->where('is_reported', false)
            ->where('is_ignored', false);
    }
}
