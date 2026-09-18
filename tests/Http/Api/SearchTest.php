<?php

declare(strict_types=1);

use App\Models\Hashtag;
use App\Models\User;

use function Pest\Laravel\getJson;

test('a guest cannot search', function (): void {
    getJson(route('api.v1.search.index', ['q' => 'ada']))->assertUnauthorized();
});

test('search requires a query', function (): void {
    $user = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    getJson(route('api.v1.search.index'), $headers)->assertUnprocessable();
});

test('search finds users by name prefix and hashtags', function (): void {
    $user = User::factory()->create();
    $ada = User::factory()->create(['name' => 'Ada Lovelace', 'username' => 'ada', 'email_verified_at' => now()]);
    User::factory()->create(['name' => 'Grace Hopper', 'username' => 'grace', 'email_verified_at' => now()]);
    Hashtag::factory()->create(['name' => 'laravel']);
    Hashtag::factory()->create(['name' => 'livewire']);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    getJson(route('api.v1.search.index', ['q' => 'ada']), $headers)
        ->assertOk()
        ->assertJsonPath('data.users.0.username', 'ada')
        ->assertJsonCount(1, 'data.users');

    getJson(route('api.v1.search.index', ['q' => 'lara']), $headers)
        ->assertOk()
        ->assertJsonPath('data.hashtags.0.name', 'laravel');

    expect($ada->username)->toBe('ada');
});
