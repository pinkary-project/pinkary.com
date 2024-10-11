<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
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
                ->orWhereIn('to_id', DB::table('followers')->select('user_id')->where('follower_id', $this->user->id));
        };

        return Question::query()
            ->select('id', 'root_id', 'parent_id', 'is_repost')
            ->withExists([
                'root as showRoot' => $followQueryClosure,
                'parent as showParent' => $followQueryClosure,
            ])
            ->with('root:id,to_id', 'root.to:id,username', 'parent:id,parent_id')
            ->whereNotNull('answer')
            ->where('is_reported', false)
            ->where('is_ignored', false)
            ->where($followQueryClosure)
            ->where(function (Builder $query): void {
                $query->whereNull('parent_id')->orWhere('showParent', true)->orWhere('showRoot', true);
            })
            ->groupBy(DB::Raw('IFNULL(root_id, id)'))
            ->orderByDesc(DB::raw('MAX(`updated_at`)'));
    }
}
