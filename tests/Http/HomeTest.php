<?php

declare(strict_types=1);

use App\Livewire\Home;
use App\Models\User;

test('guest', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect('/login');
});

test('auth', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('home'));

    $response->assertStatus(200)
        ->assertSee('Home')
        ->assertSeeLivewire(Home::class);
});
