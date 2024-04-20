<?php

declare(strict_types=1);

use App\Livewire\Home\Feed;

it('can see the "feed" view', function () {
    $response = $this->get(route('home.feed'));

    $response->assertOk()
        ->assertSee('Home')
        ->assertSeeLivewire(Feed::class);
});
