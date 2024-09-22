<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;

final readonly class TrendingQuestionsFeed
{
    /**
     * The likes bias for the trending feed.
     */
    private const int LIKES_BIAS = 1;

    /**
     * The comments bias for the trending feed.
     */
    private const int COMMENTS_BIAS = 1;

    /**
     * The time bias for the trending feed.
     */
    private const int TIME_BIAS = 86400;

    /**
     * The max days since posted for the trending feed.
     */
    private const int MAX_DAYS_SINCE_POSTED = 7;

    /**
     * Get the query builder for the feed.
     *
     * @return Builder<Question>
     */
    public function builder(): Builder
    {
        $likesBias = self::LIKES_BIAS;
        $commentsBias = self::COMMENTS_BIAS;
        $timeBias = self::TIME_BIAS;
        $maxDaysSincePosted = self::MAX_DAYS_SINCE_POSTED;

        return Question::query()
            ->select('id')
            ->from(
                Question::query()
                    ->withCount('likes', 'children')
                    ->orderByRaw(<<<SQL
                        (((likes_count * {$likesBias} + 1.0) * (children_count * {$commentsBias} + 1.0))
                        / (strftime('%s') - strftime('%s', answer_created_at) + {$timeBias} + 1.0)) desc
                    SQL)
                    ->where('is_reported', false)
                    ->where('is_ignored', false)
                    ->where('answer_created_at', '>=', now()->subDays($maxDaysSincePosted)),
                'trending_questions'
            );
    }
}
