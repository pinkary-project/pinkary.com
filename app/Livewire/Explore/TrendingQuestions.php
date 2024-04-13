<?php

declare(strict_types=1);

namespace App\Livewire\Explore;

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
        $feed = new TrendingQuestionsFeed();

        return view('livewire.explore.trending-questions', [
            'trendingQuestions' => $feed->builder()->get(),
        ]);
    }
}
