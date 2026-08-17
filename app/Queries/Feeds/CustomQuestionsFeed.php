<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Feed;
use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;

final readonly class CustomQuestionsFeed
{
    /**
     * Create a new instance of CustomQuestionsFeed.
     */
    public function __construct(
        private Feed $feed,
    ) {}

    /**
     * Get the query builder for the custom feed.
     *
     * @return Builder<Question>
     */
    public function builder(): Builder
    {
        $topicIds = $this->feed->topics()->pluck('topics.id')->all();
        $userIds = $this->feed->people()->pluck('users.id')->all();

        $matchRuleClosure = function (Builder $query) use ($topicIds, $userIds): void {
            $query->where(function (Builder $subQuery) use ($topicIds, $userIds): void {
                $hasCondition = false;

                if (! empty($topicIds)) {
                    $subQuery->whereIn('questions.topic_id', $topicIds);
                    $hasCondition = true;
                }

                if (! empty($userIds)) {
                    if ($hasCondition) {
                        $subQuery->orWhereIn('questions.to_id', $userIds);
                    } else {
                        $subQuery->whereIn('questions.to_id', $userIds);
                    }
                    $hasCondition = true;
                }

                if (! $hasCondition) {
                    $subQuery->whereRaw('1 = 0');
                }
            });
        };

        $latestQuestions = Question::query()
            ->selectRaw('id as latest_id, updated_at as last_update')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY COALESCE(root_id, id) ORDER BY updated_at DESC, id DESC) as thread_rank')
            ->whereNotNull('answer')
            ->where('is_ignored', false)
            ->where('is_reported', false)
            ->where($matchRuleClosure);

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
            ->whereNotNull('questions.answer')
            ->where('questions.is_reported', false)
            ->where('questions.is_ignored', false)
            ->where($matchRuleClosure)
            ->orderByDesc('grouped_questions.last_update');
    }
}
