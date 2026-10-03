<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
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

        $builder = $this->user->questionsReceived()->getQuery();

        $builder
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
            // A reply must not make its recipient appear to own someone else's thread.
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
