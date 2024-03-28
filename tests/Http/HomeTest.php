<?php

declare(strict_types=1);

use App\Livewire\Home;

it('can see the home view', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Home')
        ->assertSeeLivewire(Home::class);
});
