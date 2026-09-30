<?php

declare(strict_types=1);

use App\Models\Hashtag;
use App\Models\User;

test('search requires a query', function (): void {
    $this->getJson(route('api.v1.search.index'))->assertUnprocessable();

    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.search.index'), $headers)->assertUnprocessable();
});

test('a guest can search users and hashtags with neutral follow state', function (): void {
    $ada = User::factory()->create(['name' => 'Ada Lovelace', 'username' => 'ada', 'email_verified_at' => now()]);
    User::factory()->create(['name' => 'Grace Hopper', 'username' => 'grace', 'email_verified_at' => now()]);
    Hashtag::factory()->create(['name' => 'laravel']);

    $users = $this->getJson(route('api.v1.search.index', ['q' => 'ada']))
        ->assertOk()
        ->assertJsonPath('data.users.0.username', 'ada')
        ->assertJsonPath('data.users.0.followed', false)
        ->assertJsonCount(1, 'data.users');

    expect(array_keys($users->json('data.users.0')))
        ->not->toContain('email', 'id_verified', 'settings');

    $this->getJson(route('api.v1.search.index', ['q' => 'lara']))
        ->assertOk()
        ->assertJsonPath('data.hashtags.0.name', 'laravel');

    expect($ada->username)->toBe('ada');
});

test('a query that is only a sigil matches nothing instead of everything', function (): void {
    // The rules only require a non-empty string, and term() then strips
    // sigils and whitespace. A bare "@" is valid input that resolves to an
    // empty term, so it has to come back as an empty result rather than
    // reaching the search services with nothing to search for.
    User::factory()->create(['name' => 'Ada Lovelace', 'username' => 'ada']);
    Hashtag::factory()->create(['name' => 'laravel']);

    $this->getJson(route('api.v1.search.index', ['q' => '@']))
        ->assertOk()
        ->assertExactJson(['data' => ['users' => [], 'hashtags' => []]]);
});

test('a guest search never returns unverified users', function (): void {
    User::factory()->unverified()->create(['name' => 'Mallory Unverified', 'username' => 'mallory']);

    $this->getJson(route('api.v1.search.index', ['q' => 'mall']))
        ->assertOk()
        ->assertJsonCount(0, 'data.users');
});

test('search finds users by name prefix and hashtags', function (): void {
    $user = User::factory()->create();
    $ada = User::factory()->create(['name' => 'Ada Lovelace', 'username' => 'ada', 'email_verified_at' => now()]);
    User::factory()->create(['name' => 'Grace Hopper', 'username' => 'grace', 'email_verified_at' => now()]);
    Hashtag::factory()->create(['name' => 'laravel']);
    Hashtag::factory()->create(['name' => 'livewire']);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.search.index', ['q' => 'ada']), $headers)
        ->assertOk()
        ->assertJsonPath('data.users.0.username', 'ada')
        ->assertJsonCount(1, 'data.users');

    $this->getJson(route('api.v1.search.index', ['q' => 'lara']), $headers)
        ->assertOk()
        ->assertJsonPath('data.hashtags.0.name', 'laravel');

    expect($ada->username)->toBe('ada');
});
