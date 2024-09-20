<?php

declare(strict_types=1);

namespace App\Livewire\Home;

use App\Livewire\Concerns\HasLoadMore;
use App\Queries\Feeds\TrendingQuestionsFeed;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Livewire\Component;

final class TrendingQuestions extends Component
{
    use HasLoadMore;

    /**
     * Renders the component.
     */
    public function render(Request $request): View
    {
        $questions = (new TrendingQuestionsFeed())->builder()->simplePaginate($this->perPage);

        return view('livewire.home.trending-questions', [
            'trendingQuestions' => $questions,
        ]);
    }
}
