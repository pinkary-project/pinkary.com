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
            ->with([
                'from',
                'to',
                'likes',
                'likesByUser',
            ])
            ->withCount('likes')
            ->orderBy('likes_count', 'desc')
            ->where('is_reported', false)
            ->where('is_ignored', false)
            ->where('answered_at', '>=', now()->subDays(7))
            ->limit(10);
    }
}
