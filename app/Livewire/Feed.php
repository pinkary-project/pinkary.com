<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Jobs\CheckIfViewedAndIncrement;
use App\Models\Question;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

final class Feed extends Component
{
    use WithoutUrlPagination, WithPagination;

    /**
     * The component's number of questions per page.
     */
    public int $perPage = 5;

    /**
     * Load more questions.
     */
    public function loadMore(): void
    {
        $this->perPage = $this->perPage > 100 ? 100 : ($this->perPage + 5);
    }

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
        $questions = $this->getQuestions();

        dispatch(new CheckIfViewedAndIncrement(
            /* @phpstan-ignore-next-line */
            models: $questions->getCollection(),
            id: auth()->user()->id ?? request()->session()->getId(),
        ));

        return view('livewire.feed', compact('questions'));
    }

    /**
     * Get the questions.
     * @return Paginator<Question>
     */
    private function getQuestions(): Paginator
    {
        return Question::where('answer', '!=', null)
            ->where('is_ignored', false)
            ->where('is_reported', false)
            ->orderByDesc('updated_at')
            ->simplePaginate($this->perPage);
    }
}
