<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

final readonly class QuestionsFollowingFeed
{
    /**
     * Create a new instance Following feed.
     */
    public function __construct(
        private User $user,
    ) {}

    /**
     * Get the query builder for the feed.
     *
     * @return Builder<Question>
     */
    public function builder(): Builder
    {
        $followQueryClosure = function (Builder $query): void {
            $query->where('to_id', $this->user->id)
                ->orWhereExists(function (Builder|QueryBuilder $query): void {
                    $query->select(DB::raw(1))
                        ->from('followers')
                        ->whereColumn('user_id', 'to_id')
                        ->where('follower_id', $this->user->id);
                });
        };

        $latestQuestions = Question::query()
            ->selectRaw('id as latest_id, updated_at as last_update')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY COALESCE(root_id, id) ORDER BY updated_at DESC, id DESC) as thread_rank')
            ->whereNotNull('answer')
            ->where('is_ignored', false)
            ->where($followQueryClosure)
            ->where('is_reported', false);

        return Question::query()
            ->joinSub(
                $latestQuestions,
                'grouped_questions',
                'questions.id',
                '=',
                'grouped_questions.latest_id',
            )
            ->select('questions.id', 'questions.root_id', 'questions.parent_id')
            ->withExists([
                'root as showRoot' => $followQueryClosure,
                'parent as showParent' => $followQueryClosure,
            ])
            ->with('root:id,to_id', 'root.to:id,username', 'parent:id,parent_id')
            ->whereNotNull('answer')
            ->where('is_reported', false)
            ->where('is_ignored', false)
            ->where($followQueryClosure)
            ->where('grouped_questions.thread_rank', 1)
            ->where(function (Builder $query) use ($followQueryClosure): void {
                $query->whereNull('questions.parent_id')
                    ->orWhereHas('root', $followQueryClosure)
                    ->orWhereHas('parent', $followQueryClosure);
            })
            ->orderByDesc('grouped_questions.last_update');
    }
}
