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
        ->assertSee('People to follow')
        ->assertSeeLivewire(Index::class);
});

test('people to follow uses the discovery list', function () {
    $user = User::factory()->create();

    User::factory(50)
        ->hasLinks(1, function (array $attributes, User $user): array {
            return ['url' => "https://twitter.com/{$user->username}"];
        })
        ->hasQuestionsReceived(2, ['answer' => 'answer'])
        ->create();

    $outsideDiscoveryPool = User::factory()
        ->hasLinks(1, function (array $attributes, User $user): array {
            return ['url' => "https://twitter.com/{$user->username}"];
        })
        ->hasQuestionsReceived(1, ['answer' => 'answer'])
        ->create(['name' => 'Outside Discovery Pool']);

    $response = $this->actingAs($user)->get(route('bookmarks.index'));

    $response->assertOk()->assertDontSee($outsideDiscoveryPool->name);
});
