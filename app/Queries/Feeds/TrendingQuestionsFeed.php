<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final readonly class TrendingQuestionsFeed
{
    /**
     * Get the query builder for the feed.
     *
     * @return Builder<Question>
     */
    public function builder(): Builder
    {
        // (likes * 0.5 + views * 0.2) / (minutes since answered + 1) = trending score
        // the +1 is to prevent division by zero

        return Question::query()
            ->withCount('likes')
            ->orderByRaw('((`likes_count` * 0.5) + (`views` * 0.2)) / ((strftime("%s", "now") - strftime("%s", `answer_created_at`)) / 60 + 1) desc')
            ->where('is_reported', false)
            ->where('is_ignored', false)
            ->where('answer_created_at', '>=', now()->subDays(7))
            ->limit(10);
    }
}
