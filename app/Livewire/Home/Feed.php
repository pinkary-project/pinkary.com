<?php

declare(strict_types=1);

namespace App\Livewire\Home;

use App\Livewire\Concerns\HasLoadMore;
use App\Models\Question;
use App\Queries\Feeds\RecentQuestionsFeed;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class Feed extends Component
{
    use HasLoadMore;

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
        $feed = new RecentQuestionsFeed();

        return view('livewire.feed', [
            'questions' => $feed->builder()->simplePaginate($this->perPage),
        ]);
    }
}
