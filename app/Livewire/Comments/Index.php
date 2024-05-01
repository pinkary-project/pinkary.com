<?php

declare(strict_types=1);

namespace App\Livewire\Comments;

use App\Livewire\Concerns\HasLoadMore;
use App\Models\Question;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

final class Index extends Component
{
    use HasLoadMore;

    /**
     * The question ID.
     */
    #[Locked]
    public string $questionId;

    /**
     * Refresh the comments for the question.
     */
    #[On(['refresh.comments'])]
    public function refresh(): void
    {
        //
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.comments.index', [
            'comments' => Question::findOrFail($this->questionId)
                ->comments()
                ->where('is_reported', false)
                ->with('owner')
                ->oldest()
                ->simplePaginate($this->perPage),
            'questionId' => $this->questionId,
        ]);
    }
}
