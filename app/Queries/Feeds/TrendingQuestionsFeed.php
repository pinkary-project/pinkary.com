<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Config;

final readonly class TrendingQuestionsFeed
{
    /**
     * Get the query builder for the feed.
     *
     * @return Builder<Question>
     */
    public function builder(): Builder
    {
        $likesBias = Config::integer('algorithms.trending.likes_bias', 1);
        $commentsBias = Config::integer('algorithms.trending.comments_bias', 1);
        $timeBias = Config::integer('algorithms.trending.time_bias', 86400);
        $maxDaysSincePosted = Config::integer('algorithms.trending.max_days_since_posted', 7);

        return Question::query()
            ->withCount('likes', 'children')
            ->orderByRaw(<<<SQL
            (((likes_count * {$likesBias} + 1.0) * (children_count * {$commentsBias} + 1.0))
            / (unixepoch() - unixepoch(answer_created_at) + {$timeBias} + 1.0)) desc
            SQL)
            ->where('is_reported', false)
            ->where('is_ignored', false)
            ->where('answer_created_at', '>=', now()->subDays($maxDaysSincePosted));
    }
}
