<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\Bookmark;
use App\Models\Question;
use App\Models\User;

final readonly class CreateBookmark
{
    /**
     * Bookmark the question for the given user.
     */
    public function handle(Question $question, User $user): Bookmark
    {
        return $question->bookmarks()->firstOrCreate([
            'user_id' => $user->id,
        ]);
    }
}
