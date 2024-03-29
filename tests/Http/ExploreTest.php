<?php

declare(strict_types=1);

use App\Livewire\Users\Index;

it('can see the explore view', function () {
    $response = $this->get(route('explore'));

    $response->assertOk()
        ->assertSee('Explore')
        ->assertSeeLivewire(Index::class);
});
