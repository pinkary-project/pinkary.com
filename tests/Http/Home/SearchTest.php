<?php

declare(strict_types=1);

use App\Livewire\Home\Search;

it('can see the "search" view', function () {
    $response = $this->get(route('home.search'));

    $response->assertOk()
        ->assertSee('Search')
        ->assertSeeLivewire(Search::class);
});
