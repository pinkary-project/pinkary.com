<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Question;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

final class Home extends Component
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
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.home', [
            'questions' => Question::where('answer', '!=', null)
                ->where('is_reported', false)
                ->orderByDesc('answered_at')
                ->simplePaginate($this->perPage),
        ]);
    }
}
