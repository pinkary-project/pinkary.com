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
            ->where('likes_count', '>', 1)
            ->orderByDesc('likes_count')
            ->where('is_reported', false)
            ->where('is_ignored', false)
            ->where('answer_created_at', '>=', now()->subDays(7))
            ->limit(10);
    }
}
