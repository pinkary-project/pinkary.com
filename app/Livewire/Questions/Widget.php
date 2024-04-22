<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Livewire\Concerns\InteractsWithQuestion;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

final class Widget extends Component
{
    use InteractsWithQuestion;

    /**
     * The current section.
     */
    #[Locked]
    public string $currentSection;

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
}
