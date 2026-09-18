<?php

declare(strict_types=1);

use App\Models\Channel;
use App\Models\User;

use function Pest\Laravel\getJson;

test('a guest cannot list channels', function (): void {
    getJson(route('api.v1.channels.index'))->assertUnauthorized();
});

test('an authenticated user sees popular channels', function (): void {
    $user = User::factory()->create();
    Channel::factory()->create(['name' => 'Laravel', 'questions_count' => 5]);
    Channel::factory()->create(['name' => 'PHP', 'questions_count' => 2]);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    getJson(route('api.v1.channels.index'), $headers)
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Laravel')
        ->assertJsonPath('data.1.name', 'PHP');
});

test('channels can be searched by name', function (): void {
    $user = User::factory()->create();
    Channel::factory()->create(['name' => 'Laravel']);
    Channel::factory()->create(['name' => 'Cooking']);
    $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];

    getJson(route('api.v1.channels.index', ['q' => 'lara']), $headers)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Laravel');
});
