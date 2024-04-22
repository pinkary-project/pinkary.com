<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;

final readonly class FeaturedQuestionsFeed
{
    /**
     * Get the query builder for the feed.
     *
     * @return Builder<Question>
     */
    public function builder(): Builder
    {
        $featuredQuestionIds = [
            '9b7fd5db-76a5-4533-910b-ad5590ed6124',
            '9b6e38c2-15db-4cd0-a0d7-3a300e877296',
            '9b899451-e4cc-494d-aa19-cf16e66f52f6',
        ];

        return Question::query()
            ->with([
                'from',
                'to',
                'likes',
                'likesByUser',
            ])
            ->withCount('likes')
            ->whereNotNull('answer')
            ->whereIn('id', $featuredQuestionIds)
            ->where('is_ignored', false)
            ->where('is_reported', false)
            ->orderByDesc('updated_at');
    }
}
