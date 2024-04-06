<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

final class Feed extends Component
{
    use WithoutUrlPagination, WithPagination;

    /**
     * The component's amount of questions per page.
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
        $user = auth()->user();

        return view('livewire.feed', [
            'questions' => Question::query()
                ->when(
                    $user instanceof User,
                    fn (Builder $query) => $query->whereIn('to_id', $user->following()->pluck('users.id'))
                )
                ->where('is_ignored', false)
                ->where('answer', '!=', null)
                ->where('is_reported', false)
                ->orderByDesc('updated_at')
                ->simplePaginate($this->perPage),
        ]);
    }
}
