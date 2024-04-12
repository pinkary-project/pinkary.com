<?php

declare(strict_types=1);

namespace App\Livewire\Explore;

use App\Models\User;
use App\Services\QuestionFeedStrategies\ForYouFeedStrategy;
use App\Services\QuestionFeedStrategyContext;
use Illuminate\View\View;
use Livewire\Component;

final class ForYou extends Component
{
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
     * Renders the component.
     */
    public function render(): View
    {
        assert(auth()->user() instanceof User);

        $questionFeed = new QuestionFeedStrategyContext(new ForYouFeedStrategy(auth()->user()));

        return view('livewire.explore.for-you', [
            'forYouQuestions' => $questionFeed->getBuilder()->simplePaginate($this->perPage),
        ]);
    }
}
