<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use App\Models\Scopes\WhereNotModerated;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final readonly class UserQuestionsFeed
{
    /**
     * Create a new instance of UserQuestionsFeed.
     */
    public function __construct(
        private User $user,
        private ?int $viewerId = null,
    ) {}

    /**
     * A person's timeline: one row per thread, ordered by when the thread was
     * last touched.
     *
     * Unanswered questions are hidden from everyone but the profile owner.
     *
     * @param  bool  $includePinned  The clients render a pinned post as the
     *                               first card of the list rather than
     *                               filtering it out above it.
     * @return Builder<Question>
     */
    public function builder(bool $includePinned = true): Builder
    {
        // One row per thread: the most recently updated member, so a thread
        // sorts by its last activity rather than by when it began.
        $latestInThread = Question::query()
            ->selectRaw('id as latest_id, updated_at as last_update')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY COALESCE(root_id, id) ORDER BY updated_at DESC, id DESC) as thread_rank')
            ->tap(new WhereNotModerated)
            ->where('to_id', $this->user->id)
            ->when($this->user->id !== $this->viewerId, function (Builder $query): void {
                $query->whereNotNull('answer');
            });

        // The callers paginate an Eloquent Builder, so unwrap the relation
        // before building on it.
        $builder = $this->user->questionsReceived()->getQuery();

        $builder
            // The row itself plus the ancestry the client walks; whoever
            // renders the thread loads the rest.
            ->select('questions.id', 'questions.root_id', 'questions.parent_id', 'questions.pinned')
            ->joinSub(
                $latestInThread,
                'grouped_questions',
                'questions.id',
                '=',
                'grouped_questions.latest_id',
            )
            ->withExists([
                'root as showRoot' => function (Builder $query): void {
                    $query->where('to_id', $this->user->id);
                },
                'parent as showParent' => function (Builder $query): void {
                    $query->where('to_id', $this->user->id);
                },
            ])
            ->with('parent:id,parent_id')
            ->where('grouped_questions.thread_rank', 1)
            ->tap(new WhereNotModerated)
            ->when($this->user->id !== $this->viewerId, function (Builder $query): void {
                $query->whereNotNull('questions.answer');
            })
            // A reply counts only when the thread it belongs to is one this
            // person is actually part of; otherwise anyone could be dropped
            // into another's thread by a reply and appear to own it.
            ->where(function (Builder $query): void {
                $belongsToUser = function (Builder $query): void {
                    $query->where('to_id', $this->user->id);
                };

                $query->whereNull('questions.parent_id')
                    ->orWhereHas('root', $belongsToUser)
                    ->orWhereHas('parent', $belongsToUser);
            });

        if ($includePinned) {
            $builder->orderByDesc('questions.pinned');
        } else {
            $builder->where('questions.pinned', false);
        }

        return $builder
            ->orderByDesc('grouped_questions.last_update')
            ->orderByDesc('questions.id');
    }
}
