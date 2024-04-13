<?php

declare(strict_types=1);

namespace App\Livewire\Explore;

use App\Jobs\IncrementViews;
use App\Queries\Feeds\TrendingQuestionsFeed;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Livewire\Component;

final class TrendingQuestions extends Component
{
    /**
     * Renders the component.
     */
    public function render(Request $request): View
    {
        $questions = (new TrendingQuestionsFeed())->builder()->simplePaginate(5);

        /* @phpstan-ignore-next-line */
        dispatch(IncrementViews::of($questions->getCollection()));

        return view('livewire.explore.trending-questions', [
            'trendingQuestions' => $questions,
        ]);
    }
}
