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
            ->select('questions.id')
            ->whereNotNull('questions.answer')
            ->where('questions.is_ignored', false)
            ->where('questions.is_reported', false)
            ->when($this->hashtag, function (Builder $query): void {
                $query->whereHas('hashtags', function (Builder $query): void {
                    $query->where('name', 'like', $this->hashtag);
                })->orderByDesc('updated_at');
            }, function (Builder $query): void {
                $query
                    ->leftJoin('questions as root', 'root.id', '=', 'questions.root_id')
                    ->leftJoin('questions as parent', 'parent.id', '=', 'questions.parent_id')
                    ->where(function (Builder $query): void {
                        $query->whereNull('questions.parent_id')->orWhere(function (Builder $query): void {
                            $query->whereNotNull('questions.parent_id')->where('parent.is_ignored', false)->where('parent.is_reported', false);
                        });
                    })
                    ->where(function (Builder $query) {
                        $query->whereNull('questions.root_id')->orWhere(function (Builder $query): void {
                            $query->whereNotNull('questions.root_id')->where('root.is_ignored', false)->where('root.is_reported', false);
                        });
                    })
                    ->addSelect('root.id as root_id', 'parent.id as parent_id')
                    ->with('root.to:username,id', 'root:id,to_id', 'parent:id,parent_id')
                    ->groupBy(DB::Raw('IFNULL(questions.root_id, questions.id)'))
                    ->orderByDesc(DB::raw('MAX(`questions`.`updated_at`)'));
            });
    }
}
