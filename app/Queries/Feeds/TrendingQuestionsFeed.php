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
        // (likes * 0.8 + views * 0.2) / (minutes since answered + 1) = trending score
        // the +1 is to prevent division by zero
        // also if minutes since answered is greater than 100 minutes, we will use 100 minutes instead

        return Question::query()
            ->withCount('likes')
            ->where('likes_count', '>', 1)
            ->orderByRaw(<<<'SQL'
                ((`likes_count` * 0.8) + (`views` * 0.2))
                / ( IIF(
                    strftime("%s", "now") - strftime("%s", `answer_created_at`) > 6000,
                    6000,
                    strftime("%s", "now") - strftime("%s", `answer_created_at`)
                ) / 60 + 1) desc
            SQL)
            ->where('is_reported', false)
            ->where('is_ignored', false)
            ->where('answer_created_at', '>=', now()->subDays(7))
            ->limit(10);
    }
}
