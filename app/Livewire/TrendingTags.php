<?php

namespace App\Livewire;

use App\Models\Hashtag;
use Livewire\Component;

class TrendingTags extends Component
{
    public function render()
    {
        return view('livewire.trending-tags');
    }

    public function tags()
    {
        $tags = Hashtag::withCount('questions')->orderBy('questions_count', 'desc')->take(5)->get();
        return $tags;
    }
}
