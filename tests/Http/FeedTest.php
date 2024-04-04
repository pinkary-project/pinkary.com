<?php

declare(strict_types=1);

use App\Livewire\Feed;

it('can see the home view', function () {
    $response = $this->get(route('feed'));

    $response->assertOk()
        ->assertSee('Home')
        ->assertSeeLivewire(Feed::class);
});
