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
        $inspirationalUsers = Question::select('to_id')->whereRelation('likes', 'user_id', $this->user->id);

        return Question::query()
            ->whereHas('likes', fn (Builder $query) => $query->whereIn('user_id', $inspirationalUsers))
            ->orderByDesc('updated_at')
            ->whereNotNull('answer')
            ->where('is_reported', false)
            ->where('is_ignored', false);
    }
}
