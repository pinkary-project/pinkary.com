<?php

declare(strict_types=1);

use App\Livewire\Home\TrendingQuestions;

it('can see the "trending" view', function () {
    $response = $this->get(route('home.trending'));

    $response->assertOk()
        ->assertSee('Trending')
        ->assertSeeLivewire(TrendingQuestions::class);
});
