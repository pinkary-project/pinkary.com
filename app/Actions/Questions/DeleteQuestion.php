<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\Question;

final readonly class DeleteQuestion
{
    public function handle(Question $question): void
    {
        $question->delete();
    }
}
