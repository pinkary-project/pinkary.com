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
            ->select('questions.id', 'questions.root_id', 'questions.parent_id')
            ->whereNotNull('answer')
            ->where('is_ignored', false)
            ->where('is_reported', false)
            ->when($this->hashtag, function (Builder $query): void {
                $query->whereHas('hashtags', function (Builder $query): void {
                    $query
                    // using 'like' for this query (with no wildcards) will
                    // result in a case-insensitive lookup from sqlite,
                    // which is what we want.
                        ->where('name', 'like', $this->hashtag);
                })->orderByDesc('updated_at');
            }, function (Builder $query): void {
                $latestQuestions = Question::query()
                    ->selectRaw('id as latest_id, updated_at as last_update')
                    ->selectRaw('ROW_NUMBER() OVER (PARTITION BY COALESCE(root_id, id) ORDER BY updated_at DESC, id DESC) as thread_rank')
                    ->whereNotNull('answer')
                    ->where('is_ignored', false)
                    ->where('is_reported', false);

                $query->joinSub(
                    $latestQuestions,
                    'grouped_questions',
                    'questions.id',
                    '=',
                    'grouped_questions.latest_id',
                )
                    ->where('grouped_questions.thread_rank', 1)
                    ->with('root.to:username,id', 'root:id,to_id', 'parent:id,parent_id')
                    ->orderByDesc('grouped_questions.last_update');
            });
    }
}
