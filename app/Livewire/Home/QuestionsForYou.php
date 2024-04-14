<?php

declare(strict_types=1);

namespace App\Livewire\Home;

use App\Livewire\Concerns\HasLoadMore;
use App\Models\User;
use App\Queries\Feeds\QuestionsForYouFeed;
use Illuminate\View\View;
use Livewire\Component;

final class QuestionsForYou extends Component
{
    use HasLoadMore;

    /**
     * Renders the component.
     */
    public function render(): View
    {
        $user = type(auth()->user())->as(User::class);

        $feed = new QuestionsForYouFeed($user);

        return view('livewire.home.questions-for-you', [
            'forYouQuestions' => $feed->builder()->simplePaginate($this->perPage),
        ]);
    }
}
