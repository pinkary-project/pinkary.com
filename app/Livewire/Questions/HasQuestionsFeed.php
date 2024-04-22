<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Livewire\Concerns\HasLoadMore;
use App\Models\User;
use App\Queries\Feeds\FeaturedQuestionsFeed;
use App\Queries\Feeds\QuestionsForYouFeed;
use App\Queries\Feeds\RecentQuestionsFeed;
use App\Queries\Feeds\TrendingQuestionsFeed;
use Illuminate\Contracts\Pagination\Paginator;

trait HasQuestionsFeed
{
    use HasLoadMore;

    /**
     * Get the questions feed.
     *
     * @return \Illuminate\Contracts\Pagination\Paginator<\App\Models\Question>
     */
    public function feed(): Paginator
    {
        return (new RecentQuestionsFeed)
            ->builder()
            ->simplePaginate($this->perPage);
    }

    /**
     * Get the questions feed.
     *
     * @return \Illuminate\Pagination\Paginator<\App\Models\Question>
     */
    public function forYou(): Paginator
    {
        $user = type(auth()->user())->as(User::class);

        return (new QuestionsForYouFeed($user))
            ->builder()
            ->simplePaginate($this->perPage);
    }

    /**
     * Get the questions feed.
     *
     * @return \Illuminate\Pagination\Paginator<\App\Models\Question>
     */
    public function trending(): Paginator
    {
        return (new TrendingQuestionsFeed)
            ->builder()
            ->simplePaginate($this->perPage);
    }

    /**
     * Get the questions feed.
     *
     * @return \Illuminate\Pagination\Paginator<\App\Models\Question>
     */
    public function featured(): Paginator
    {
        return (new FeaturedQuestionsFeed)
            ->builder()
            ->simplePaginate($this->perPage);
    }
}
