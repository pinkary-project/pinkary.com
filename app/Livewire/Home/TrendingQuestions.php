<?php

declare(strict_types=1);

namespace App\Livewire\Home;

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
        $questions = (new TrendingQuestionsFeed())->builder()->get();

        dispatch(IncrementViews::of($questions));

        return view('livewire.home.trending-questions', [
            'trendingQuestions' => $questions,
        ]);
    }
}
