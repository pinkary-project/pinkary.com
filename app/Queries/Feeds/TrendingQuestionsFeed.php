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

        $order = DB::raw('((`likes_count` * 0.5) + (`views` * 0.2)) / ((strftime("%s", "now") - strftime("%s", `answered_at`)) / 60 + 1)');

        // for MySQL use this: if we move to MySQL, we need to change the query
        // $order = DB::raw('((`likes_count` * 0.5) + (`views` * 0.2)) / ((UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`answered_at`)) / 60 + 1)');

        return Question::query()
            ->withCount('likes')
            ->orderBy($order, 'desc')
            ->where('is_reported', false)
            ->where('is_ignored', false)
            ->where('answer_created_at', '>=', now()->subDays(7))
            ->limit(10);
    }
}
