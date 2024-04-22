<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\Question;
use Livewire\Attributes\On;

trait CanBeIgnorable
{
    /**
     * Ignore the given question.
     */
    #[On('question.ignore')]
    public function ignore(string $questionId): void
    {
        $question = Question::findOrFail($questionId);

        $this->authorize('ignore', $question);

        $question->update(['is_ignored' => true]);

        $this->dispatch('question.ignored');
    }
}
