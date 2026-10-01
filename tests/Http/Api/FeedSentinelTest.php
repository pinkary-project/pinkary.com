<?php

declare(strict_types=1);

use App\Models\User;

test('the feed never leaks the shared update sentinel', function (): void {
    $user = User::factory()->create();

    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Hello, Pinkary.',
    ], ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken])
        ->assertCreated();

    $this->getJson(route('api.v1.feed.index'))
        ->assertOk()
        ->assertJsonPath('data.0.is_update', true)
        ->assertJsonPath('data.0.content', null)
        ->assertJsonPath('data.0.answer', 'Hello, Pinkary.');
});

test('a profile never leaks the shared update sentinel', function (): void {
    $user = User::factory()->create();

    $this->postJson(route('api.v1.questions.store'), [
        'content' => 'Hello, Pinkary.',
    ], ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken])
        ->assertCreated();

    $this->getJson(route('api.v1.users.questions.index', $user->username))
        ->assertOk()
        ->assertJsonPath('data.0.is_update', true)
        ->assertJsonPath('data.0.content', null)
        ->assertJsonPath('data.0.answer', 'Hello, Pinkary.');
});
