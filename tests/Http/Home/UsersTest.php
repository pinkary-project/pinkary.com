<?php

declare(strict_types=1);

use App\Livewire\Home\Users;

it('can see the "users" view', function () {
    $response = $this->get(route('home.users'));

    $response->assertOk()
        ->assertSee('Search')
        ->assertSeeLivewire(Users::class);
});
