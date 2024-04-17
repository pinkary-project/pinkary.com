<?php

declare(strict_types=1);

use App\Jobs\IncrementViews;
use App\Livewire\Home\Feed;

it('can see the "feed" view', function () {
    $response = $this->get(route('home.feed'));

    $response->assertOk()
        ->assertSee('Home')
        ->assertSeeLivewire(Feed::class);
});

it('does increment views', function () {
    Queue::fake(IncrementViews::class);

    $this->get(route('home.feed'));

    Queue::assertPushed(IncrementViews::class);
});
