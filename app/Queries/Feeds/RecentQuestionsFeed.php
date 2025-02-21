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
            ->select('questions.*')
            ->whereNotNull('answer')
            ->where('is_ignored', false)
            ->where('is_reported', false)
            ->when($this->hashtag, function (Builder $query): void {
                $query->whereHas('hashtags', function (Builder $query): void {
                    $query->where('name', 'like', $this->hashtag);
                })->orderByDesc('updated_at');
            }, function (Builder $query): void {
                $query->joinSub(
                    Question::select(DB::raw('IFNULL(root_id, id) as group_id'))
                        ->selectRaw('MAX(updated_at) as last_update')
                        ->whereNotNull('answer')
                        ->where('is_ignored', false)
                        ->where('is_reported', false)
                        ->groupBy(DB::raw('IFNULL(root_id, id)')),
                    'grouped_questions',
                    function ($join) {
                        $join->on(DB::raw('IFNULL(questions.root_id, questions.id)'), '=', 'grouped_questions.group_id')
                            ->whereRaw('questions.updated_at = grouped_questions.last_update');
                    }
                )
                ->with('root.to:username,id', 'root:id,to_id', 'parent:id,parent_id')
                ->orderByDesc('grouped_questions.last_update');
            });
    }
}
