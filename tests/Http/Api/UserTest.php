<?php

declare(strict_types=1);

use App\Models\Link;
use App\Models\Question;
use App\Models\User;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

test('a guest cannot view users or follow', function (): void {
    $user = User::factory()->create();

    getJson(route('api.v1.users.show', $user->username))->assertUnauthorized();
    postJson(route('api.v1.users.follow', $user->username))->assertUnauthorized();
    deleteJson(route('api.v1.users.unfollow', $user->username))->assertUnauthorized();
});

test('any user card carries stats, links, and follow state', function (): void {
    $me = User::factory()->create();
    $ada = User::factory()->create(['name' => 'Ada Lovelace', 'username' => 'ada', 'bio' => 'First programmer.']);
    Link::factory()->create(['user_id' => $ada->id, 'description' => 'Website', 'url' => 'https://example.com', 'is_visible' => true]);
    Link::factory()->create(['user_id' => $ada->id, 'description' => 'Hidden', 'url' => 'https://example.com/hidden', 'is_visible' => false]);
    Question::factory()->create(['to_id' => $ada->id, 'answer' => 'An answer.']);
    $headers = ['Authorization' => 'Bearer '.$me->createToken('test')->plainTextToken];

    getJson(route('api.v1.users.show', 'ada'), $headers)
        ->assertOk()
        ->assertJsonPath('data.name', 'Ada Lovelace')
        ->assertJsonPath('data.bio', 'First programmer.')
        ->assertJsonPath('data.is_me', false)
        ->assertJsonPath('data.followed_by_me', false)
        ->assertJsonPath('data.email', null)
        ->assertJsonPath('data.stats.posts', 1)
        ->assertJsonCount(1, 'data.links')
        ->assertJsonPath('data.links.0.description', 'Website')
        ->assertJsonPath('data.links.0.url', 'https://example.com?ref=pinkary');
});

test('following and unfollowing is idempotent', function (): void {
    $me = User::factory()->create();
    $ada = User::factory()->create(['username' => 'ada']);
    $headers = ['Authorization' => 'Bearer '.$me->createToken('test')->plainTextToken];

    postJson(route('api.v1.users.follow', 'ada'), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.followed', true)
        ->assertJsonPath('data.followers', 1);

    postJson(route('api.v1.users.follow', 'ada'), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.followers', 1);

    getJson(route('api.v1.users.show', 'ada'), $headers)
        ->assertOk()
        ->assertJsonPath('data.followed_by_me', true)
        ->assertJsonPath('data.stats.followers', 1);

    deleteJson(route('api.v1.users.unfollow', 'ada'), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.followed', false)
        ->assertJsonPath('data.followers', 0);

    deleteJson(route('api.v1.users.unfollow', 'ada'), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.followers', 0);
});

test('users cannot follow themselves', function (): void {
    $me = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$me->createToken('test')->plainTextToken];

    postJson(route('api.v1.users.follow', $me->username), [], $headers)->assertForbidden();
    deleteJson(route('api.v1.users.unfollow', $me->username), [], $headers)->assertForbidden();
});

test('link clicks are recorded once per visitor per day', function (): void {
    $me = User::factory()->create();
    $ada = User::factory()->create(['username' => 'ada']);
    $link = Link::factory()->create(['user_id' => $ada->id, 'is_visible' => true]);
    $headers = ['Authorization' => 'Bearer '.$me->createToken('test')->plainTextToken];

    postJson(route('api.v1.links.click', $link), [], $headers)->assertOk();
    postJson(route('api.v1.links.click', $link), [], $headers)->assertOk();

    expect($link->fresh()->click_count)->toBe(1);
});

test('own link taps do not count', function (): void {
    $me = User::factory()->create();
    $link = Link::factory()->create(['user_id' => $me->id, 'is_visible' => true]);
    $headers = ['Authorization' => 'Bearer '.$me->createToken('test')->plainTextToken];

    postJson(route('api.v1.links.click', $link), [], $headers)->assertOk();

    expect($link->fresh()->click_count)->toBe(0);
});

test('viewing a user profile dispatches IncrementViews job', function (): void {
    Illuminate\Support\Facades\Queue::fake();

    $me = User::factory()->create();
    User::factory()->create(['username' => 'ada']);
    $headers = ['Authorization' => 'Bearer '.$me->createToken('test')->plainTextToken];

    getJson(route('api.v1.users.show', 'ada'), $headers)->assertOk();

    Illuminate\Support\Facades\Queue::assertPushed(App\Jobs\IncrementViews::class);
});
