<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;

final readonly class FeedItems
{
    /**
     * Combine regular posts and repost events into one feed query.
     *
     * @param  Builder<Question>  $questions
     * @param  Builder<\App\Models\Repost>  $reposts
     * @return Builder<Question>
     */
    public function merge(Builder $questions, Builder $reposts): Builder
    {
        $feedItems = $questions->unionAll($reposts);

        return Question::query()
            ->joinSub($feedItems, 'feed_items', 'feed_items.question_id', '=', 'questions.id')
            ->select(
                'questions.*',
                'feed_items.repost_id',
                'feed_items.reposted_by_id',
                'feed_items.feed_at',
            );
    }
}
