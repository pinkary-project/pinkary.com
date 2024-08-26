<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;

final readonly class RecentQuestionsFeed
{
    /**
     * Create a new instance of the RecentQuestionsFeed.
     */
    public function __construct(
        private ?string $hashtag = null,
    ) {}

    /**
     * Get the query builder for the feed.
     *
     * @return Builder<Question>
     */
    public function builder(): Builder
    {
        return Question::query()
            ->where('answer', '!=', null)
            ->where('is_ignored', false)
            ->where('is_reported', false)
            ->when($this->hashtag, fn (Builder $query) => $query
                ->whereHas('hashtags', fn ($query) => $query
                    // using 'like' for this query (with no wildcards) will
                    // result in a case-insensitive lookup from sqlite,
                    // which is what we want.
                    ->where('name', 'like', $this->hashtag)
                )
            )
            ->orderByDesc('updated_at');
    }
}
