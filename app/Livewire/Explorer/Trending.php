<?php

declare(strict_types=1);

namespace App\Livewire\Explorer;

use App\Jobs\IncrementViews;
use App\Queries\Feeds\TrendingQuestionsFeed;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Livewire\Component;

final class Trending extends Component
{
    /**
     * Renders the component.
     */
    public function render(Request $request): View
    {
        $questions = (new TrendingQuestionsFeed())->builder()->get();

        IncrementViews::dispatchUsingSession($questions);

        return view('livewire.explorer.trending', [
            'trendingQuestions' => $questions,
        ]);
    }
}
