<?php

declare(strict_types=1);

use App\Jobs\IncrementViews;
use App\Livewire\Feed;

it('can see the home view', function () {
    Queue::fake(IncrementViews::class);
    $response = $this->get(route('feed'));

    $response->assertOk()
        ->assertSee('Home')
        ->assertSeeLivewire(Feed::class);

    Queue::assertPushed(IncrementViews::class);
});
