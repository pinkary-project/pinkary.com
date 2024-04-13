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
            ->limit(10)
            ->whereHas('likes')
            ->where('is_reported', false)
            ->where('is_ignored', false)
            ->where('created_at', '>=', now()->subHours(12));
    }
}
