<?php

declare(strict_types=1);

use App\Jobs\IncrementViews;
use App\Livewire\Explorer\Trending;

it('can see the "trending" view', function () {
    $response = $this->get(route('home.explorer', 'trending'));

    $response->assertOk()
        ->assertSee('Trending')
        ->assertSeeLivewire(Trending::class);
});

it('does increment views', function () {
    Queue::fake(IncrementViews::class);

    $this->get(route('home.explorer', 'trending'));

    Queue::assertPushed(IncrementViews::class);
});
