<?php

declare(strict_types=1);

use App\Actions\Users\CreateFollow;
use App\Models\User;

use function Pest\Laravel\getJson;

test('a guest cannot view followers or following', function (): void {
    $user = User::factory()->create();

    getJson(route('api.v1.users.followers.index', $user->username))->assertUnauthorized();
    getJson(route('api.v1.users.following.index', $user->username))->assertUnauthorized();
});

test('an authenticated user can view paginated followers and following with followed_by_me state', function (): void {
    $me = User::factory()->create();
    $target = User::factory()->create(['username' => 'target']);
    $follower = User::factory()->create(['name' => 'Follower One']);
    $followingUser = User::factory()->create(['name' => 'Following One']);

    $createFollow = app(CreateFollow::class);
    $createFollow->handle($follower, $target->id);
    $createFollow->handle($target, $followingUser->id);
    $createFollow->handle($me, $follower->id); // I follow target's follower

    $headers = ['Authorization' => 'Bearer '.$me->createToken('test')->plainTextToken];

    // Followers of target
    getJson(route('api.v1.users.followers.index', 'target'), $headers)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $follower->id)
        ->assertJsonPath('data.0.followed_by_me', true);

    // Following of target
    getJson(route('api.v1.users.following.index', 'target'), $headers)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $followingUser->id)
        ->assertJsonPath('data.0.followed_by_me', false);
});
