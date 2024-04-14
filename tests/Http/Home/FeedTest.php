<?php

declare(strict_types=1);

use App\Jobs\IncrementViews;
use App\Livewire\Home\Feed;

it('can see the "feed" view', function () {
    Queue::fake(IncrementViews::class);
    $response = $this->get(route('home.feed'));

    $response->assertOk()
        ->assertSee('Home')
        ->assertSeeLivewire(Feed::class);

    Queue::assertPushed(IncrementViews::class);
});
