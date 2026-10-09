<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\Question;
use App\Models\Repost;
use App\Models\User;

final readonly class CreateRepost
{
    /**
     * Repost the question for the given user.
     */
    public function handle(Question $question, User $user): Repost
    {
        return $question->reposts()->firstOrCreate([
            'user_id' => $user->id,
        ]);
    }
}
