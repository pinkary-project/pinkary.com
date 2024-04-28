<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final readonly class RecentQuestionsFeed
{
    /**
     * Get the query builder for the feed.
     *
     * @return Builder<Question>
     */
    public function builder(): Builder
    {
        $user = auth()->user();

        return Question::query()
            ->where('answer', '!=', null)
            ->when(
                $user instanceof User,
                fn (Builder $query) => $query->whereIn('to_id', $user->following()->pluck('users.id'))
            )
            ->where('is_ignored', false)
            ->where('is_reported', false)
            ->orderByDesc('updated_at');
    }
}
