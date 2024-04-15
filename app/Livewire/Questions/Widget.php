<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Livewire\Concerns\HasLoadMore;
use App\Livewire\Concerns\InteractsWithQuestion;
use App\Models\Question;
use App\Queries\Feeds\RecentQuestionsFeed;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

final class Widget extends Component
{
    use HasLoadMore;
    use InteractsWithQuestion;

    /**
     * Whether the pinned label should be displayed or not.
     */
    #[Locked]
    public bool $pinnable = false;

    #[Computed(cache: false, key: 'questions')]
    public function questions(): Paginator
    {
        $feed = new RecentQuestionsFeed();

        return $feed
            ->builder()
            ->simplePaginate($this->perPage);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.questions.widget');
    }

    /**
     * Refresh the component.
     */
    #[On('question.created')]
    #[On('question.updated')]
    #[On('question.reported')]
    public function refresh(): void
    {
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
}
