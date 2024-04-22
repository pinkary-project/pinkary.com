<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Contracts\QuestionsFeed;
use App\Livewire\Concerns\InteractsWithQuestion;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

final class Widget extends Component implements QuestionsFeed
{
    use HasQuestionsFeed;
    use InteractsWithQuestion;

    /**
     * The current section.
     */
    #[Locked]
    public string $currentSection;

    #[Computed(cache: false, key: 'questions')]
    public function questions(): Paginator
    {
        return match ($this->currentSection) {
            'for-you' => $this->forYou(),
            'trending' => $this->trending(),
            'featured' => $this->featured(),
            default => $this->feed(),
        };
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
}
