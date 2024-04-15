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
        return Question::where('answer', '!=', null)
            ->with(['from', 'to', 'likes', 'likesByUser'])
            ->withCount('likes')
            ->where('is_ignored', false)
            ->where('is_reported', false)
            ->orderByDesc('updated_at');
    }
}
