<?php

declare(strict_types=1);

use App\Models\User;

test('guests can browse verified users across pages with neutral follow state', function (): void {
    User::factory()->unverified()->create(['username' => '00-unverified']);
    $ada = User::factory()->create(['name' => 'Ada Lovelace', 'username' => 'ada']);
    $grace = User::factory()->create(['name' => 'Grace Hopper', 'username' => 'grace']);

    $firstPage = $this->getJson(route('api.v1.users.directory', ['per_page' => 1]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $ada->id)
        ->assertJsonPath('data.0.followed_by_me', false)
        ->assertJsonPath('data.0.follows_me', false)
        ->assertJsonPath('data.0.stats.followers', 0)
        ->assertJsonPath('data.0.stats.following', 0);

    expect($firstPage->json('links.next'))->toBeString()->toContain('page=2');

    $this->getJson(route('api.v1.users.directory', ['per_page' => 1, 'page' => 2]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $grace->id)
        ->assertJsonPath('links.next', null);
});

test('authenticated directory results exclude the viewer and show both follow directions', function (): void {
    $viewer = User::factory()->create();
    $followed = User::factory()->create(['username' => 'followed']);
    $incoming = User::factory()->create(['username' => 'incoming']);
    $popular = User::factory()->create(['username' => 'popular']);
    $popularFollowers = User::factory(2)->unverified()->create();

    $viewer->following()->attach($followed->id);
    $incoming->following()->attach($viewer->id);
    $popularFollowers->each(fn (User $follower) => $follower->following()->attach($popular->id));

    $this->getJson(route('api.v1.users.directory', ['per_page' => 50]), [
        'Authorization' => 'Bearer '.$viewer->createToken('test')->plainTextToken,
    ])
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.id', $followed->id)
        ->assertJsonPath('data.0.followed_by_me', true)
        ->assertJsonPath('data.0.follows_me', false)
        ->assertJsonPath('data.1.id', $popular->id)
        ->assertJsonPath('data.1.followed_by_me', false)
        ->assertJsonPath('data.1.follows_me', false)
        ->assertJsonPath('data.2.id', $incoming->id)
        ->assertJsonPath('data.2.followed_by_me', false)
        ->assertJsonPath('data.2.follows_me', true);
});
