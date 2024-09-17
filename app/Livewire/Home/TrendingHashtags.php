<?php

namespace App\Livewire\Home;

use Livewire\Component;
use App\Queries\Feeds\TrendingHashtags as TrendingHashtagsQuery;

class TrendingHashtags extends Component
{
    public function render()
    {
        $hashtags = (new TrendingHashtagsQuery())->builder()->limit(5)->orderBy('questions_count', 'DESC')->get();
        return view('livewire.home.trending-hashtags', ['hashtags' => $hashtags]);
    }
}
