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
        $tempTableName = 'questions_from_following_'.$this->user->id;

        DB::statement("DROP TABLE IF EXISTS $tempTableName");
        DB::statement("CREATE TEMPORARY TABLE $tempTableName AS ".$this->questionsFromFollowing()->toRawSql());

        return Question::query()
            ->from($tempTableName)
            ->select(
                'id',
                'root_id',
                'parent_id',
                DB::raw("EXISTS(SELECT 1 FROM $tempTableName as subTable WHERE subTable.id = $tempTableName.root_id) as showRoot"),
                DB::raw("EXISTS(SELECT 1 FROM $tempTableName as subTable WHERE subTable.id = $tempTableName.parent_id) as showParent")
            )
            ->with('root:id,to_id', 'root.to:id,username', 'parent:id,parent_id')
            ->where(function (Builder $query): void {
                $query->whereNull('parent_id')->orWhere('showParent', true)->orWhere('showRoot', true);
            })
            ->groupBy(DB::Raw('IFNULL(root_id, id)'))
            ->orderByDesc(DB::raw('MAX(`updated_at`)'));
    }

    /**
     * Get the query for questions from users the current user is following.
     *
     * @return Builder<Question>
     */
    private function questionsFromFollowing(): Builder
    {
        return Question::query()
            ->where(function (Builder $query): void {
                $query->where('to_id', $this->user->id)
                    ->orWhereIn('to_id', DB::table('followers')->select('user_id')->where('follower_id', $this->user->id));
            })
            ->whereNotNull('answer')
            ->where('is_reported', false)
            ->where('is_ignored', false);
    }
}
