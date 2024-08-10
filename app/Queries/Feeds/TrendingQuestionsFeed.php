<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;

final readonly class TrendingQuestionsFeed
{
    /**
     * Get the query builder for the feed.
     *
     * @return Builder<Question>
     */
    public function builder(): Builder
    {
        return Question::query()
            ->withCount('likes')
            ->orderBy('likes_count', 'desc')
            ->where('is_reported', false)
            ->where('is_ignored', false)
            ->where(fn (Builder $query) => $query
                ->where('is_update', false)
                ->whereHas('answer', fn (Builder $query) => $query
                    ->where('created_at', '>=', now()->subDays(7))
                )
            )
            ->orWhere(fn (Builder $query) => $query
                ->where('is_update', true)
                ->where('created_at', '>=', now()->subDays(7))
                ->whereDoesntHave('answer')
            )
            ->limit(10);
    }
}
