<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\Question;

final readonly class UpdateQuestionStatus
{
    /**
     * Update moderation status flags on the question.
     */
    public function handle(Question $question, bool $ignored = false, bool $reported = false): void
    {
        $attributes = [];

        if ($ignored) {
            $attributes['is_ignored'] = true;
        }

        if ($reported) {
            $attributes['is_reported'] = true;
        }

        if ($attributes !== []) {
            $question->update($attributes);
        }
    }
}
