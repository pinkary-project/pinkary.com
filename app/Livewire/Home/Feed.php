<?php

declare(strict_types=1);

namespace App\Livewire\Home;

use App\Jobs\IncrementViews;
use App\Models\Question;
use App\Queries\Feeds\RecentQuestionsFeed;
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
        // TODO: Remove this. It's never used. Ignoring is handled in question.show.
        $question = Question::findOrFail($questionId);

        $this->authorize('ignore', $question);

        $question->update(['is_ignored' => true]);

        $this->dispatch('question.ignored');
    }

    /**
     * Refresh the feed.
     */
    #[On('question.created')]
    public function refresh(): void {}

    /**
     * Render the component.
     */
    public function render(): View
    {
        $questions = (new RecentQuestionsFeed())->builder()->cursorPaginate();

        IncrementViews::dispatchUsingSession($questions->getCollection());

        return view('livewire.feed', [
            'questions' => $questions,
        ]);
    }
}
