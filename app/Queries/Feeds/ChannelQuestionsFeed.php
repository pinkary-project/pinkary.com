<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Channel;
use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;

final readonly class ChannelQuestionsFeed
{
    /**
     * Create a new instance of the ChannelQuestionsFeed.
     */
    public function __construct(
        private Channel $channel,
    ) {}

    /**
     * Get the query builder for the feed.
     *
     * @return Builder<Question>
     */
    public function builder(): Builder
    {
        return Question::query()
            ->where('channel_id', $this->channel->id)
            ->whereNotNull('answer')
            ->where('is_ignored', false)
            ->where('is_reported', false)
            ->orderByDesc('updated_at');
    }
}
