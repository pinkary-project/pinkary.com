<?php

declare(strict_types=1);

use App\Livewire\Bookmarks\Index;
use App\Models\User;

test('guest', function () {
    $response = $this->get(route('bookmarks.index'));

    $response->assertRedirect(route('login'));
});

test('auth', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('bookmarks.index'))
        ->assertStatus(200);

    $response->assertOk()
        ->assertSee('Bookmarks')
        ->assertSeeLivewire(Index::class);
});
