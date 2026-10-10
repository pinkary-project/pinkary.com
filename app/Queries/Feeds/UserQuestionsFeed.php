<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use App\Models\Repost;
use App\Models\Scopes\WhereNotModerated;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final readonly class UserQuestionsFeed
{
    /** Configure the user's profile feed. */
    public function __construct(
        private User $user,
        private ?int $viewerId = null,
    ) {}

    /**
     * @return Builder<Question>
     */
    public function builder(bool $includePinned = true): Builder
    {
        // Rank threads by their latest activity, not their original posting date.
        $latestInThread = Question::query()
            ->selectRaw('id as latest_id, updated_at as last_update')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY COALESCE(root_id, id) ORDER BY updated_at DESC, id DESC) as thread_rank')
            ->tap(new WhereNotModerated)
            ->where('to_id', $this->user->id)
            ->when($this->user->id !== $this->viewerId, function (Builder $query): void {
                $query->whereNotNull('answer');
            });

        $questions = $this->user->questionsReceived()->getQuery()
            ->selectRaw('questions.id as question_id')
            ->selectRaw('NULL as repost_id, NULL as reposted_by_id, grouped_questions.last_update as feed_at')
            ->joinSub(
                $latestInThread,
                'grouped_questions',
                'questions.id',
                '=',
                'grouped_questions.latest_id',
            )
            ->where('grouped_questions.thread_rank', 1)
            ->tap(new WhereNotModerated)
            ->when($this->user->id !== $this->viewerId, function (Builder $query): void {
                $query->whereNotNull('questions.answer');
            })
            // A reply must not make its recipient appear to own someone else's thread.
            ->where(function (Builder $query): void {
                $belongsToUser = function (Builder $query): void {
                    $query->where('to_id', $this->user->id);
                };

                $query->whereNull('questions.parent_id')
                    ->orWhereHas('root', $belongsToUser)
                    ->orWhereHas('parent', $belongsToUser);
            });

        $reposts = Repost::query()
            ->join('questions', 'questions.id', '=', 'reposts.question_id')
            ->selectRaw('questions.id as question_id')
            ->selectRaw('reposts.id as repost_id, reposts.user_id as reposted_by_id, reposts.created_at as feed_at')
            ->where('reposts.user_id', $this->user->id)
            ->whereNotNull('questions.answer')
            ->where('questions.is_ignored', false)
            ->where('questions.is_reported', false);

        $builder = new FeedItems()->merge($questions, $reposts)
            ->withExists([
                'root as showRoot' => function (Builder $query): void {
                    $query->where('to_id', $this->user->id);
                },
                'parent as showParent' => function (Builder $query): void {
                    $query->where('to_id', $this->user->id);
                },
            ])
            ->with('parent:id,parent_id');

        if ($includePinned) {
            $builder->orderByRaw('CASE WHEN feed_items.repost_id IS NULL THEN questions.pinned ELSE 0 END DESC');
        } else {
            $builder->where(function (Builder $query): void {
                $query->where('questions.pinned', false)
                    ->orWhereNotNull('feed_items.repost_id');
            });
        }

        return $builder
            ->orderByDesc('feed_items.feed_at')
            ->orderByDesc('questions.id');
    }
}
