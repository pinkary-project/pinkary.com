<?php

declare(strict_types=1);

namespace App\Livewire\Home;

use App\Livewire\Concerns\HasLoadMore;
use App\Models\User;
use App\Queries\Feeds\QuestionsFollowingFeed;
use Illuminate\View\View;
use Livewire\Component;

final class QuestionsFollowing extends Component
{
    use HasLoadMore;

    /**
     * Renders the component.
     */
    public function render(): View
    {
        $user = type(auth()->user())->as(User::class);

        $questions = (new QuestionsFollowingFeed($user))->builder()->simplePaginate($this->perPage);

        return view('livewire.home.questions-following', [
            'followingQuestions' => $questions,
        ]);
    }
}
