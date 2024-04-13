<?php

declare(strict_types=1);

namespace App\Livewire\Explore;

use App\Jobs\IncrementViews;
use App\Models\User;
use App\Queries\Feeds\QuestionsForYouFeed;
use Illuminate\View\View;
use Livewire\Component;

final class QuestionsForYou extends Component
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
        $user = type(auth()->user())->as(User::class);

        $questions = (new QuestionsForYouFeed($user))->builder()->simplePaginate($this->perPage);

        /* @phpstan-ignore-next-line */
        dispatch(IncrementViews::of($questions->getCollection()));

        return view('livewire.explore.questions-for-you', [
            'forYouQuestions' => $questions,
        ]);
    }
}
