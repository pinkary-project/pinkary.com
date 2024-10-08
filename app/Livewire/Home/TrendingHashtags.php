<?php

namespace App\Livewire\Home;

use Livewire\Component;
use App\Queries\Feeds\TrendingHashtags as TrendingHashtagsQuery;
use App\Models\Hashtag;
use Illuminate\Database\Eloquent\Builder;

class TrendingHashtags extends Component
{
    public function render()
    {
        $hashtags = Hashtag::query()
        ->withCount(['questions' => function (Builder $query) {
            $query->where('created_at', '>=', now()->subDays(1));
        }])
        ->where('updated_at', '>=', now()->subDays(1))->limit(5)->orderBy('questions_count', 'DESC')->get();
        return view('livewire.home.trending-hashtags', ['hashtags' => $hashtags]);
    }
}
