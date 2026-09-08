<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\Question;
use App\Models\User;

final readonly class CreateLike
{
    /**
     * Like the question for the given user.
     */
    public function handle(Question $question, User $user): void
    {
        $question->likes()->firstOrCreate([
            'user_id' => $user->id,
        ]);
    }
}
