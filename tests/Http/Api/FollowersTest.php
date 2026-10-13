<?php

declare(strict_types=1);

use App\Actions\Users\CreateFollow;
use App\Models\User;

test('a guest can read followers and following with neutral follow state', function (): void {
    $me = User::factory()->create();
    $target = User::factory()->create(['username' => 'target']);
    $follower = User::factory()->create(['name' => 'Follower One', 'email' => 'follower@example.com']);
    $followingUser = User::factory()->create(['name' => 'Following One']);

    $createFollow = app(CreateFollow::class);
    $createFollow->handle($follower, $target->id);
    $createFollow->handle($target, $followingUser->id);
    $createFollow->handle($me, $follower->id);

    $followers = $this->getJson(route('api.v1.users.followers.index', 'target'))->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $follower->id)
        ->assertJsonPath('data.0.name', 'Follower One')
        ->assertJsonPath('data.0.is_me', false)
        ->assertJsonPath('data.0.followed_by_me', false)
        ->assertJsonPath('data.0.email', null);

    expect(array_keys($followers->json('data.0')))
        ->not->toContain('settings', 'password', 'remember_token', 'mail_preference_time');

    $this->getJson(route('api.v1.users.following.index', 'target'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $followingUser->id)
        ->assertJsonPath('data.0.followed_by_me', false)
        ->assertJsonPath('data.0.email', null);
});

test('follow lists carry both directions of the follow state, like the web', function (): void {
    $me = User::factory()->create();
    $target = User::factory()->create(['username' => 'target']);

    $mutual = User::factory()->create(['name' => 'Mutual']);
    $theyFollowMe = User::factory()->create(['name' => 'They Follow Me']);
    $iFollowThem = User::factory()->create(['name' => 'I Follow Them']);
    $neither = User::factory()->create(['name' => 'Neither']);

    $createFollow = app(CreateFollow::class);
    foreach ([$mutual, $theyFollowMe, $iFollowThem, $neither] as $follower) {
        $createFollow->handle($follower, $target->id);
    }

    $createFollow->handle($me, $mutual->id);
    $createFollow->handle($me, $iFollowThem->id);
    $createFollow->handle($mutual, $me->id);
    $createFollow->handle($theyFollowMe, $me->id);

    $headers = ['Authorization' => 'Bearer '.$me->createToken('test')->plainTextToken];

    $followers = $this->getJson(route('api.v1.users.followers.index', 'target'), $headers)
        ->assertOk()
        ->json('data');

    $byName = collect($followers)->keyBy('name');

    expect($byName)->toHaveKeys(['Mutual', 'They Follow Me', 'I Follow Them', 'Neither'])
        ->and($byName['Mutual']['follows_me'])->toBeTrue()
        ->and($byName['Mutual']['followed_by_me'])->toBeTrue()
        ->and($byName['They Follow Me']['follows_me'])->toBeTrue()
        ->and($byName['They Follow Me']['followed_by_me'])->toBeFalse()
        ->and($byName['I Follow Them']['follows_me'])->toBeFalse()
        ->and($byName['I Follow Them']['followed_by_me'])->toBeTrue()
        ->and($byName['Neither']['follows_me'])->toBeFalse()
        ->and($byName['Neither']['followed_by_me'])->toBeFalse();
});

test('guests get a false follows_me rather than a leaked or guessed state', function (): void {
    $me = User::factory()->create();
    $target = User::factory()->create(['username' => 'target']);

    app(CreateFollow::class)->handle($target, $me->id);

    $this->getJson(route('api.v1.users.followers.index', $me->username))
        ->assertOk()
        ->assertJsonPath('data.0.username', $target->username)
        ->assertJsonPath('data.0.follows_me', false)
        ->assertJsonPath('data.0.followed_by_me', false);
});

test('list rows do not report confident zero counts they never loaded', function (): void {
    $me = User::factory()->create();
    $target = User::factory()->create(['username' => 'target']);

    app(CreateFollow::class)->handle($me, $target->id);

    $this->getJson(route('api.v1.users.followers.index', 'target'))
        ->assertOk()
        ->assertJsonPath('data.0.stats.followers', null)
        ->assertJsonPath('data.0.stats.posts', null);
});

test('a guest cannot read an unknown profile followers', function (): void {
    $this->getJson(route('api.v1.users.followers.index', 'nobody'))->assertNotFound();
    $this->getJson(route('api.v1.users.following.index', 'nobody'))->assertNotFound();
});

test('an authenticated user can view paginated followers and following with followed_by_me state', function (): void {
    $me = User::factory()->create();
    $target = User::factory()->create(['username' => 'target']);
    $follower = User::factory()->create(['name' => 'Follower One']);
    $followingUser = User::factory()->create(['name' => 'Following One']);

    $createFollow = app(CreateFollow::class);
    $createFollow->handle($follower, $target->id);
    $createFollow->handle($target, $followingUser->id);
    $createFollow->handle($me, $follower->id);

    $headers = ['Authorization' => 'Bearer '.$me->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.users.followers.index', 'target'), $headers)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $follower->id)
        ->assertJsonPath('data.0.followed_by_me', true);

    $this->getJson(route('api.v1.users.following.index', 'target'), $headers)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $followingUser->id)
        ->assertJsonPath('data.0.followed_by_me', false);
});
