<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;

final readonly class BookmarkedQuestionsFeed
{
    public function __construct(private int $userId) {}

    /**
     * @return Builder<Question>
     */
    public function builder(): Builder
    {
        return Question::query()
            ->select('questions.id')
            ->join('bookmarks', 'bookmarks.question_id', '=', 'questions.id')
            ->where('bookmarks.user_id', $this->userId)
            ->orderByDesc('bookmarks.created_at');
    }
}
