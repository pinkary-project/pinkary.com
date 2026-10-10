<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use App\Models\Repost;
use App\Models\Scopes\WhereNotModerated;
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
        $questions = Question::query()
            ->selectRaw('questions.id as question_id')
            ->selectRaw('NULL as repost_id, NULL as reposted_by_id')
            ->whereNotNull('answer')
            ->tap(new WhereNotModerated);

        if ($this->hashtag !== null && $this->hashtag !== '') {
            return $questions
                ->select('questions.id', 'questions.root_id', 'questions.parent_id', 'questions.pinned')
                ->selectRaw('NULL as repost_id, NULL as reposted_by_id, questions.updated_at as feed_at')
                ->whereHas('hashtags', function (Builder $query): void {
                    // using 'like' for this query (with no wildcards) will
                    // result in a case-insensitive lookup from sqlite,
                    // which is what we want.
                    $query
                        ->where('name', 'like', $this->hashtag);
                })
                ->with('root.to:username,id', 'root:id,to_id', 'parent:id,parent_id')
                ->orderByDesc('questions.updated_at');
        }

        $latestQuestions = Question::query()
            ->selectRaw('id as latest_id, updated_at as last_update')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY COALESCE(root_id, id) ORDER BY updated_at DESC, id DESC) as thread_rank')
            ->whereNotNull('answer')
            ->tap(new WhereNotModerated);

        $questions
            ->joinSub(
                $latestQuestions,
                'grouped_questions',
                'questions.id',
                '=',
                'grouped_questions.latest_id',
            )
            ->where('grouped_questions.thread_rank', 1)
            ->selectRaw('grouped_questions.last_update as feed_at');

        $reposts = Repost::query()
            ->join('questions', 'questions.id', '=', 'reposts.question_id')
            ->selectRaw('questions.id as question_id')
            ->selectRaw('reposts.id as repost_id, reposts.user_id as reposted_by_id, reposts.created_at as feed_at')
            ->whereNotNull('questions.answer')
            ->where('questions.is_ignored', false)
            ->where('questions.is_reported', false);

        return new FeedItems()->merge($questions, $reposts)
            ->with('root.to:username,id', 'root:id,to_id', 'parent:id,parent_id')
            ->orderByDesc('feed_items.feed_at')
            ->orderByDesc('questions.id');
    }
}
