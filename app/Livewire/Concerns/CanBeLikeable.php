<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\Question;

trait CanBeLikeable
{
    /**
     * Like the question.
     */
    public function like(string $questionId): void
    {
        $question = Question::findOrFail($questionId);

        $question->likes()->firstOrCreate([
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Unlike the question.
     */
    public function unlike(string $questionId): void
    {
        $question = Question::findOrFail($questionId);

        if ($like = $question->likes()->where('user_id', auth()->id())->first()) {
            $this->authorize('delete', $like);

            $like->delete();
        }
    }
}
