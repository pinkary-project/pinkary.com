<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
        // Check if the database connection is SQLite
        $isSQLite = DB::getDriverName() === 'sqlite';

        // Use strftime for SQLite, otherwise use EXTRACT(EPOCH FROM ...)
        $timeCalculation = $isSQLite
            ? "strftime('%s', 'now') - strftime('%s', questions.answer_created_at)"
            : "EXTRACT(EPOCH FROM NOW()) - EXTRACT(EPOCH FROM questions.answer_created_at)";

        return Question::query()
            ->leftJoin('likes', 'likes.question_id', '=', 'questions.id')
            ->leftJoin('questions as child_questions', 'child_questions.parent_id', '=', 'questions.id')
            ->select('questions.*', DB::raw('COUNT(DISTINCT likes.id) as likes_count'), DB::raw('COUNT(DISTINCT child_questions.id) as children_count'))
            ->where('questions.is_reported', false)
            ->where('questions.is_ignored', false)
            ->where('questions.answer_created_at', '>=', now()->subDays($maxDaysSincePosted))
            ->groupBy('questions.id')
            ->orderByRaw(
                <<<SQL
          (((COALESCE(COUNT(DISTINCT likes.id), 0) * {$likesBias} + 1.0) * (COALESCE(COUNT(DISTINCT child_questions.id), 0) * {$commentsBias} + 1.0))
          / ({$timeCalculation} + {$timeBias} + 1.0)) desc
          SQL
            );
    }
}
