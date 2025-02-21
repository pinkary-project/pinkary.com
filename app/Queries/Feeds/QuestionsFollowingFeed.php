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

        if (config('database.default') === 'sqlite') {
            return Question::query()
                ->select('id', 'root_id', 'parent_id')
                ->withExists([
                    'root as showRoot' => $followQueryClosure,
                    'parent as showParent' => $followQueryClosure,
                ])
                ->with('root:id,to_id', 'root.to:id,username', 'parent:id,parent_id')
                ->whereNotNull('answer')
                ->where('is_reported', false)
                ->where('is_ignored', false)
                ->where($followQueryClosure)
                ->havingRaw('parent_id IS NULL or showRoot = 1 or showParent = 1')
                ->groupBy(DB::Raw('IFNULL(root_id, id)'))
                ->orderByDesc(DB::raw('MAX(`updated_at`)'));
        }

        return Question::query()
            ->joinSub(
                Question::select(DB::raw('IFNULL(root_id, id) as group_id'))
                    ->selectRaw('MAX(updated_at) as last_update')
                    ->whereNotNull('answer')
                    ->where('is_ignored', false)
                    ->where($followQueryClosure)
                    ->where('is_reported', false)
                    ->groupBy(DB::raw('IFNULL(root_id, id)')),
                'grouped_questions',
                function ($join) {
                    $join->on(DB::raw('IFNULL(questions.root_id, questions.id)'), '=', 'grouped_questions.group_id')
                        ->whereRaw('questions.updated_at = grouped_questions.last_update');
                }
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
            ->havingRaw('parent_id IS NULL or showRoot = 1 or showParent = 1')
            ->orderByDesc('grouped_questions.last_update');
    }
}
