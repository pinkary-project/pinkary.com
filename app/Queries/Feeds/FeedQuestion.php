<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

final readonly class FeedQuestion
{
    /**
     * @param  Builder<Question>  $query
     * @return Builder<Question>
     */
    public function __invoke(Builder $query, ?int $userId): Builder
    {
        return $query
            // Select the cached parse to avoid HTTP fetches and row updates during reads.
            ->addSelect('questions.id', 'questions.from_id', 'questions.to_id', 'questions.content', 'questions.answer', 'questions.parsed', 'questions.anonymously', 'questions.views', 'questions.created_at', 'questions.answer_created_at', 'questions.answer_updated_at', 'questions.parent_id', 'questions.root_id', 'questions.channel_id', 'questions.poll_expires_at', 'questions.pinned')
            ->with([
                'from:id,name,username,avatar,is_verified,is_company_verified',
                'to:id,name,username,avatar,is_verified,is_company_verified',
                'repostedBy:id,name,username,avatar,is_verified,is_company_verified',
                'channel:id,name,slug',
                'pollOptions' => fn (Relation $query) => $query->select('id', 'question_id', 'text', 'votes_count')->orderBy('id'),
                'pollVotes' => fn (Relation $query) => $query
                    ->select('id', 'question_id', 'poll_option_id')
                    ->when(
                        $userId,
                        fn (Builder $q) => $q->where('user_id', $userId),
                        fn (Builder $q) => $q->whereRaw('1 = 0'),
                    ),
            ])
            ->withExists([
                'likes as is_liked' => fn (Builder $query) => $query->when(
                    $userId,
                    fn (Builder $q) => $q->where('user_id', $userId),
                    fn (Builder $q) => $q->whereRaw('1 = 0'),
                ),
                'bookmarks as is_bookmarked' => fn (Builder $query) => $query->when(
                    $userId,
                    fn (Builder $q) => $q->where('user_id', $userId),
                    fn (Builder $q) => $q->whereRaw('1 = 0'),
                ),
            ])
            ->withCount(['likes', 'children', 'bookmarks', 'reposts'])
            ->withExists([
                'reposts as is_reposted' => fn (Builder $query) => $query->when(
                    $userId,
                    fn (Builder $q) => $q->where('user_id', $userId),
                    fn (Builder $q) => $q->whereRaw('1 = 0'),
                ),
            ]);
    }
}
