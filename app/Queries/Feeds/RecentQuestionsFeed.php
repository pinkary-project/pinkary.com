<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;

final readonly class RecentQuestionsFeed
{
    /**
     * Get the query builder for the feed.
     *
     * @return Builder<Question>
     */
    public function builder(): Builder
    {
        return Question::query()
            ->where('is_ignored', false)
            ->where('is_reported', false)
            ->where(fn (Builder $query) => $query
                ->where('is_update', false)
                ->whereHas('answer')
            )
            ->orWhere(fn (Builder $query) => $query
                ->where('is_update', true)
                ->whereDoesntHave('answer')
            )
            ->orderByDesc('updated_at');
    }
}
