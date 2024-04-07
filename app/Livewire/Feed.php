<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Question;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class Feed extends Component
{
    /**
     * The component's amount of questions per page.
     */
    private int $perPage = 10;

    /**
     *  The component will load data for this page
     */
    private int $page = 1;

    /**
     * Mount the component.
     */
    public function mount(int $page = 1): void
    {
        $this->page = $page;
    }

    public function placeholder(): string
    {
        return <<<'HTML'
            <div>
                <!-- todo: make a good place holder -->
            </div>
        HTML;
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
        return view('livewire.feed', [
            'questions' => Question::where('answer', '!=', null)
                ->where('is_ignored', false)
                ->where('is_reported', false)
                ->orderByDesc('updated_at')
                ->simplePaginate($this->perPage, page: $this->page),
        ]);
    }
}
