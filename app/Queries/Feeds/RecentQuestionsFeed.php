<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final readonly class RecentQuestionsFeed
{
    /**
     * Get the query builder for the feed.
     *
     * @return Builder<Question>
     */
    public function builder(): Builder
    {
        return Question::query()
            ->where(function (Builder $query): void {
                $query->whereNotNull('answer')->orWhere('from_id', DB::raw('`to_id`'));
            })
            ->where('is_ignored', false)
            ->where('is_reported', false)
            ->orderByDesc('updated_at');
    }
}
