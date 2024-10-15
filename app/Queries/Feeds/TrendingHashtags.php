<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Hashtag;
use Illuminate\Database\Eloquent\Builder;

final readonly class TrendingHashtags
{

    /**
     * The max days since posted for the trending feed.
     */
    private const int MAX_DAYS_SINCE_POSTED = 1;

    /**
     * Get the query builder for the feed.
     *
     * @return Builder<Question>
     */
    public function builder(): Builder
    {
        $maxDaysSincePosted = self::MAX_DAYS_SINCE_POSTED;

        return Hashtag::query()
            ->withCount('questions')
            ->where('created_at', '>=', now()->subDays($maxDaysSincePosted));
    }
}
