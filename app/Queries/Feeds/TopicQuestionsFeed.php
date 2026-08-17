<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Builder;

final readonly class TopicQuestionsFeed
{
    /**
     * The likes bias for trending.
     */
    private const int LIKES_BIAS = 1;

    /**
     * The comments bias for trending.
     */
    private const int COMMENTS_BIAS = 1;

    /**
     * The time bias for trending.
     */
    private const int TIME_BIAS = 86400;

    /**
     * The max days since posted for trending.
     */
    private const int MAX_DAYS_SINCE_POSTED = 7;

    /**
     * Create a new instance of the TopicQuestionsFeed.
     */
    public function __construct(
        private Topic $topic,
        private string $sort = 'recent',
    ) {}

    /**
     * Get the query builder for the feed.
     *
     * @return Builder<Question>
     */
    public function builder(): Builder
    {
        if ($this->sort === 'trending') {
            $likesBias = self::LIKES_BIAS;
            $commentsBias = self::COMMENTS_BIAS;
            $timeBias = self::TIME_BIAS;
            $maxDaysSincePosted = self::MAX_DAYS_SINCE_POSTED;

            $trendingQuery = Question::query()
                ->select('id')
                ->where('topic_id', $this->topic->id)
                ->where('is_reported', false)
                ->where('is_ignored', false)
                ->where('answer_created_at', '>=', now()->subDays($maxDaysSincePosted))
                ->withCount('likes', 'children')
                ->orderByRaw(<<<SQL
                    (((likes_count * {$likesBias} + 1.0) * (children_count * {$commentsBias} + 1.0))
                    / (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(answer_created_at) + {$timeBias} + 1.0)) desc
                SQL);

            return Question::query()
                ->joinSub($trendingQuery, 'trending_ids', 'questions.id', '=', 'trending_ids.id')
                ->select('questions.id', 'questions.root_id', 'questions.parent_id', 'questions.topic_id')
                ->with('root.to:username,id', 'root:id,to_id', 'parent:id,parent_id', 'topic');
        }

        $latestQuestions = Question::query()
            ->selectRaw('id as latest_id, updated_at as last_update')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY COALESCE(root_id, id) ORDER BY updated_at DESC, id DESC) as thread_rank')
            ->whereNotNull('answer')
            ->where('is_ignored', false)
            ->where('is_reported', false)
            ->where('topic_id', $this->topic->id);

        return Question::query()
            ->joinSub(
                $latestQuestions,
                'grouped_questions',
                'questions.id',
                '=',
                'grouped_questions.latest_id',
            )
            ->select('questions.id', 'questions.root_id', 'questions.parent_id', 'questions.topic_id')
            ->where('grouped_questions.thread_rank', 1)
            ->with('root.to:username,id', 'root:id,to_id', 'parent:id,parent_id', 'topic')
            ->orderByDesc('grouped_questions.last_update');
    }
}
