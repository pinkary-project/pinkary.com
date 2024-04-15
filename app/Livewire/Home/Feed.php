<?php

declare(strict_types=1);

namespace App\Livewire\Home;

use App\Livewire\Concerns\HasLoadMore;
use App\Models\Question;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class Feed extends Component
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

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.feed');
    }
}
