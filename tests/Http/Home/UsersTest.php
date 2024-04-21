<?php

declare(strict_types=1);

use App\Livewire\Explorer\Users;

it('can see the "users" view', function () {
    $response = $this->get(route('home.explorer', 'users'));

    $response->assertOk()
        ->assertSee('Users')
        ->assertSeeLivewire(Users::class);
});
