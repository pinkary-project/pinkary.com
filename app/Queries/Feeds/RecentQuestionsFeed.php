<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final readonly class RecentQuestionsFeed
{
    /**
     * Create a new instance of the RecentQuestionsFeed.
     */
    public function __construct(
        private ?string $hashtag = null,
    ) {}

    /**
     * Get the query builder for the feed.
     *
     * @return Builder<Question>
     */
    public function builder(): Builder
    {
        return Question::query()
            ->select('id')
            ->whereNotNull('answer')
            ->where('is_ignored', false)
            ->where('is_reported', false)
            ->when($this->hashtag, function (Builder $query): void {
                $query->whereHas('hashtags', function (Builder $query): void {
                    $query
                    // using 'like' for this query (with no wildcards) will
                    // result in a case-insensitive lookup from sqlite,
                    // which is what we want.
                        ->where('name', 'like', $this->hashtag);
                })->orderByDesc('updated_at');
            }, function (Builder $query): void {
                $query->addSelect('root_id', 'parent_id')
                    ->with('root.to:username,id', 'root:id,to_id', 'parent:id,parent_id')
                    ->groupBy(DB::Raw('IFNULL(root_id, id)'))
                    ->orderByDesc(DB::raw('MAX(`updated_at`)'));
            });
    }
}
