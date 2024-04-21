<?php

declare(strict_types=1);

namespace App\Livewire\Explorer;

use App\Jobs\IncrementViews;
use App\Livewire\Concerns\HasLoadMore;
use App\Models\User;
use App\Queries\Feeds\QuestionsForYouFeed;
use Illuminate\View\View;
use Livewire\Component;

final class ForYou extends Component
{
    use HasLoadMore;

    /**
     * Renders the component.
     */
    public function render(): View
    {
        if (! auth()->check()) {
            return view('livewire.explorer.for-you');
        }

        $user = type(auth()->user())->as(User::class);

        $questions = (new QuestionsForYouFeed($user))->builder()->simplePaginate($this->perPage);

        IncrementViews::dispatchUsingSession($questions->getCollection());

        return view('livewire.explorer.for-you', [
            'forYouQuestions' => $questions,
        ]);
    }
}
