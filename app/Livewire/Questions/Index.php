<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Jobs\IncrementViews;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

final class Index extends Component
{
    use WithoutUrlPagination, WithPagination;

    /**
     * The component's user ID.
     */
    #[Locked]
    public int $userId;

    /**
     * The component's per page count.
     */
    public int $perPage = 5;

    /**
     * Whether the pinned label should be displayed or not.
     */
    #[Locked]
    public bool $pinnable = false;

    /**
     * Load more questions.
     */
    public function loadMore(): void
    {
        $this->perPage = ($this->perPage > 100) ? 100 : ($this->perPage + 5);
    }

    /**
     * Render the component.
     */
    public function render(Request $request): View
    {
        $user = User::findOrFail($this->userId);

        $questions = $user
            ->questionsReceived()
            ->where('is_ignored', false)
            ->where('is_reported', false)
            ->when(! $user->is($request->user()), function (Builder $query, bool $_): void { // @phpstan-ignore-line
                $query->whereNotNull('answer');
            })
            ->orderByDesc('pinned')
            ->orderByDesc('updated_at')
            ->simplePaginate($this->perPage);

        dispatch(new IncrementViews(
            /* @phpstan-ignore-next-line */
            models: $questions->getCollection(),
            id: $request->user()?->id ?? $request->session()->getId(),
        ));

        return view('livewire.questions.index', [
            'user' => $user,
            'questions' => $questions,
        ]);
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
