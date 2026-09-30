<?php

declare(strict_types=1);

use App\Models\Link;
use App\Models\Question;
use App\Models\User;

test('a guest cannot follow a user', function (): void {
    $user = User::factory()->create();

    $this->postJson(route('api.v1.users.follow', $user->username))->assertUnauthorized();
    $this->deleteJson(route('api.v1.users.unfollow', $user->username))->assertUnauthorized();
});

test('a guest can read a profile without private fields', function (): void {
    $ada = User::factory()->create([
        'name' => 'Ada Lovelace',
        'username' => 'ada',
        'bio' => 'First programmer.',
        'email' => 'ada@example.com',
    ]);
    Link::factory()->create(['user_id' => $ada->id, 'description' => 'Website', 'url' => 'https://example.com', 'is_visible' => true]);
    Question::factory()->create(['to_id' => $ada->id, 'answer' => 'An answer.']);

    $response = $this->getJson(route('api.v1.users.show', 'ada'))->assertOk()
        ->assertJsonPath('data.name', 'Ada Lovelace')
        ->assertJsonPath('data.bio', 'First programmer.')
        ->assertJsonPath('data.is_me', false)
        ->assertJsonPath('data.followed_by_me', false)
        ->assertJsonPath('data.email', null)
        ->assertJsonPath('data.verification.email', null)
        ->assertJsonPath('data.stats.posts', 1)
        ->assertJsonCount(1, 'data.links');

    expect(array_keys($response->json('data')))
        ->not->toContain('two_factor_secret', 'settings', 'password', 'remember_token', 'mail_preference_time');
});

test('a bearer token still resolves the viewer on a public profile read', function (): void {
    $me = User::factory()->create();
    $ada = User::factory()->create(['username' => 'ada']);

    $me->following()->attach($ada->id);

    $this->getJson(route('api.v1.users.show', 'ada'), [
        'Authorization' => 'Bearer '.$me->createToken('test')->plainTextToken,
    ])
        ->assertOk()
        ->assertJsonPath('data.is_me', false)
        ->assertJsonPath('data.followed_by_me', true);
});

test('any user card carries stats, links, and follow state', function (): void {
    $me = User::factory()->create();
    $ada = User::factory()->create(['name' => 'Ada Lovelace', 'username' => 'ada', 'bio' => 'First programmer.']);
    Link::factory()->create(['user_id' => $ada->id, 'description' => 'Website', 'url' => 'https://example.com', 'is_visible' => true]);
    Link::factory()->create(['user_id' => $ada->id, 'description' => 'Hidden', 'url' => 'https://example.com/hidden', 'is_visible' => false]);
    Question::factory()->create(['to_id' => $ada->id, 'answer' => 'An answer.']);
    $headers = ['Authorization' => 'Bearer '.$me->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.users.show', 'ada'), $headers)
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

    $this->postJson(route('api.v1.users.follow', 'ada'), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.followed', true)
        ->assertJsonPath('data.followers', 1);

    $this->postJson(route('api.v1.users.follow', 'ada'), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.followers', 1);

    $this->getJson(route('api.v1.users.show', 'ada'), $headers)
        ->assertOk()
        ->assertJsonPath('data.followed_by_me', true)
        ->assertJsonPath('data.stats.followers', 1);

    $this->deleteJson(route('api.v1.users.unfollow', 'ada'), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.followed', false)
        ->assertJsonPath('data.followers', 0);

    $this->deleteJson(route('api.v1.users.unfollow', 'ada'), [], $headers)
        ->assertOk()
        ->assertJsonPath('data.followers', 0);
});

test('users cannot follow themselves', function (): void {
    $me = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$me->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.users.follow', $me->username), [], $headers)->assertForbidden();
    $this->deleteJson(route('api.v1.users.unfollow', $me->username), [], $headers)->assertForbidden();
});

test('link clicks are recorded once per visitor per day', function (): void {
    $me = User::factory()->create();
    $ada = User::factory()->create(['username' => 'ada']);
    $link = Link::factory()->create(['user_id' => $ada->id, 'is_visible' => true]);
    $headers = ['Authorization' => 'Bearer '.$me->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.links.click', $link), [], $headers)->assertOk();
    $this->postJson(route('api.v1.links.click', $link), [], $headers)->assertOk();

    expect($link->fresh()->click_count)->toBe(1);
});

test('guest link clicks are recorded, because the web counts them too', function (): void {
    $ada = User::factory()->create(['username' => 'ada']);
    $link = Link::factory()->create(['user_id' => $ada->id, 'is_visible' => true]);

    // profile/show.blade.php mounts the same Livewire component whose
    // non-owner branch calls click() with auth()->id() === null for a
    // guest, and UpdateLinkClicks counts anyone who is not the owner.
    $this->postJson(route('api.v1.links.click', $link))->assertOk();

    expect($link->fresh()->click_count)->toBe(1);
});

test('clicks on a hidden link are not recorded', function (): void {
    $ada = User::factory()->create(['username' => 'ada']);
    $link = Link::factory()->create(['user_id' => $ada->id, 'is_visible' => false]);

    $this->postJson(route('api.v1.links.click', $link))->assertNotFound();

    expect($link->fresh()->click_count)->toBe(0);
});

test('own link taps do not count', function (): void {
    $me = User::factory()->create();
    $link = Link::factory()->create(['user_id' => $me->id, 'is_visible' => true]);
    $headers = ['Authorization' => 'Bearer '.$me->createToken('test')->plainTextToken];

    $this->postJson(route('api.v1.links.click', $link), [], $headers)->assertOk();

    expect($link->fresh()->click_count)->toBe(0);
});

test('viewing a user profile dispatches IncrementViews job', function (): void {
    Illuminate\Support\Facades\Queue::fake();

    $me = User::factory()->create();
    User::factory()->create(['username' => 'ada']);
    $headers = ['Authorization' => 'Bearer '.$me->createToken('test')->plainTextToken];

    $this->getJson(route('api.v1.users.show', 'ada'), $headers)->assertOk();

    Illuminate\Support\Facades\Queue::assertPushed(App\Jobs\IncrementViews::class);
});

test('a guest profile read does not inflate the view counter', function (): void {
    Illuminate\Support\Facades\Queue::fake();

    User::factory()->create(['username' => 'ada']);

    $this->getJson(route('api.v1.users.show', 'ada'))->assertOk();

    Illuminate\Support\Facades\Queue::assertNotPushed(App\Jobs\IncrementViews::class);
});

test('public reads share a rate limited bucket that is not the write bucket', function (): void {
    $user = User::factory()->create(['username' => 'ada']);

    // Every public read route draws from the same per-visitor budget.
    for ($i = 0; $i < 60; $i++) {
        $this->getJson(route('api.v1.users.show', 'ada'))->assertOk();
    }

    $this->getJson(route('api.v1.users.show', 'ada'))->assertStatus(429);
    $this->getJson(route('api.v1.users.followers.index', 'ada'))->assertStatus(429);
    $this->getJson(route('api.v1.users.questions.index', 'ada'))->assertStatus(429);
});

test('a read bucket never throttles a write route', function (): void {
    $me = User::factory()->create();
    $ada = User::factory()->create(['username' => 'ada']);
    $headers = ['Authorization' => 'Bearer '.$me->createToken('test')->plainTextToken];

    for ($i = 0; $i < 60; $i++) {
        $this->getJson(route('api.v1.users.show', 'ada'))->assertOk();
    }

    $this->getJson(route('api.v1.users.show', 'ada'))->assertStatus(429);

    $this->postJson(route('api.v1.users.follow', 'ada'), [], $headers)->assertOk();
});

test('following route is rate limited', function (): void {
    $me = User::factory()->create();
    $headers = ['Authorization' => 'Bearer '.$me->createToken('test')->plainTextToken];

    for ($i = 0; $i < 15; $i++) {
        $target = User::factory()->create();
        $this->postJson(route('api.v1.users.follow', $target->username), [], $headers)->assertOk();
    }

    $exceededTarget = User::factory()->create();
    $this->postJson(route('api.v1.users.follow', $exceededTarget->username), [], $headers)
        ->assertStatus(429);
});
