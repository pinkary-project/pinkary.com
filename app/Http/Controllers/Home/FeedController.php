<?php

declare(strict_types=1);

namespace App\Http\Controllers\Home;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Livewire\Livewire;

final class FeedController
{
    private bool $isInfiniteScrollRequest = false;

    public function __invoke(Request $request): View|string
    {
        $this->isInfiniteScrollRequest = $request->hasHeader('X-Alpine-Request');

        // I set this up so that we can see how the htmx handling is done using:
        //  1. Livewire component rendering
        //  2. Regular blade views with fragments
        // Just change this bool value to switch between the options.
        // Obviously we would pick one option for production and make
        // this code normal.
        $handleWithLivewireFeed = true;

        return $handleWithLivewireFeed
            ? $this->livewireFeed()
            : $this->normalViewWithFragment();
    }

    private function livewireFeed(): string|View
    {
        return $this->isInfiniteScrollRequest
            ? Livewire::mount('home.feed') // render the component and return its html string fragment 👀
            : view('home.feed');
    }

    private function normalViewWithFragment(): View|string
    {
        $questions = (new App\Queries\Feeds\RecentQuestionsFeed())
            ->builder()
            ->cursorPaginate();

        IncrementViews::dispatchUsingSession($questions->getCollection());

        return view('home.feed', ['questions' => $questions])
            ->fragmentIf($this->isInfiniteScrollRequest, 'questions-list');
    }
}
