<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Accounts;

beforeEach(function (): void {
    cookie()->unqueue('accounts');
    request()->cookies->remove('accounts');
});

test('all returns empty array when no accounts cookie exists', function (): void {
    $accounts = Accounts::all();

    expect($accounts)->toBeEmpty();
});

test('all returns accounts from cookie', function (): void {
    $user1 = User::factory()->create(['username' => 'john']);
    $user2 = User::factory()->create(['username' => 'jane']);

    $cookieData = json_encode([
        'john' => ['id' => $user1->id, 'hash' => app(Accounts::class)->hashFor($user1)],
        'jane' => ['id' => $user2->id, 'hash' => app(Accounts::class)->hashFor($user2)],
    ]);
    request()->cookies->set('accounts', $cookieData);

    $accounts = Accounts::all();

    expect($accounts)->toHaveKeys(['john', 'jane'])
        ->and($accounts['john']->id)->toBe($user1->id)
        ->and($accounts['jane']->id)->toBe($user2->id);
});

test('all handles invalid json in cookie', function (): void {
    request()->cookies->set('accounts', 'invalid-json');

    $accounts = Accounts::all();

    expect($accounts)->toBeEmpty();
});

test('all prunes account when remember token hash does not match', function (): void {
    $user1 = User::factory()->create(['username' => 'john']);
    $user2 = User::factory()->create(['username' => 'jane']);

    $cookieData = json_encode([
        'john' => ['id' => $user1->id, 'hash' => app(Accounts::class)->hashFor($user1)],
        'jane' => ['id' => $user2->id, 'hash' => 'invalid-hash'],
    ]);
    request()->cookies->set('accounts', $cookieData);

    $accounts = Accounts::all();

    expect($accounts)->toHaveKey('john')
        ->and($accounts)->not->toHaveKey('jane');
});

test('all seamlessly updates cookie when username changes', function (): void {
    $user = User::factory()->create(['username' => 'original_name']);

    Accounts::push($user);

    $user->update(['username' => 'new_name']);

    $accounts = Accounts::all();

    expect($accounts)->toHaveKey('new_name')
        ->and($accounts)->not->toHaveKey('original_name');
});

test('push adds account to cookie', function (): void {
    $user = User::factory()->create(['username' => 'testuser']);

    Accounts::push($user);

    $queuedCookies = cookie()->getQueuedCookies();
    $accountsCookie = collect($queuedCookies)->last(fn ($cookie): bool => $cookie->getName() === 'accounts');

    expect($accountsCookie)->not()->toBeNull();

    $accounts = json_decode($accountsCookie->getValue(), true);
    expect($accounts)->toHaveKey('testuser')
        ->and($accounts['testuser']['id'])->toBe($user->id)
        ->and($accounts['testuser']['hash'])->toBe(app(Accounts::class)->hashFor($user));
});

test('push generates remember token if empty', function (): void {
    $user = User::factory()->create(['remember_token' => null]);

    Accounts::push($user);

    expect($user->fresh()->remember_token)->not->toBeNull();
});

test('push adds multiple accounts to cookie', function (): void {
    $user1 = User::factory()->create(['username' => 'user1']);
    $user2 = User::factory()->create(['username' => 'user2']);

    Accounts::push($user1);
    Accounts::push($user2);

    $queuedCookies = cookie()->getQueuedCookies();
    $accountsCookie = collect($queuedCookies)->last(fn ($cookie): bool => $cookie->getName() === 'accounts');

    $accounts = json_decode($accountsCookie->getValue(), true);
    expect($accounts)->toHaveKeys(['user1', 'user2']);
});

test('switch authenticates user when account exists', function (): void {
    $user = User::factory()->create(['username' => 'testuser']);

    Accounts::push($user);

    Accounts::switch('testuser');

    expect(auth()->check())->toBeTrue()
        ->and(auth()->user()->username)->toBe('testuser');
});

test('switch throws exception when account not found in cookie', function (): void {
    User::factory()->create(['username' => 'testuser']);

    expect(fn () => Accounts::switch('testuser'))
        ->toThrow('Unauthorized action.');
});

test('remove deletes account from cookie', function (): void {
    $user1 = User::factory()->create(['username' => 'user1']);
    $user2 = User::factory()->create(['username' => 'user2']);

    Accounts::push($user1);
    Accounts::push($user2);

    Accounts::remove('user1');

    $queuedCookies = cookie()->getQueuedCookies();
    $accountsCookie = collect($queuedCookies)->last(fn ($cookie): bool => $cookie->getName() === 'accounts');

    $accounts = json_decode($accountsCookie->getValue(), true);
    expect($accounts)->not->toHaveKey('user1')
        ->toHaveKey('user2');
});

test('remove handles non-existent account gracefully', function (): void {
    $user1 = User::factory()->create(['username' => 'user1']);

    Accounts::push($user1);
    Accounts::remove('nonexistent');

    $queuedCookies = cookie()->getQueuedCookies();
    $accountsCookie = collect($queuedCookies)->last(fn ($cookie): bool => $cookie->getName() === 'accounts');

    $accounts = json_decode($accountsCookie->getValue(), true);
    expect($accounts)->toHaveKey('user1');
});

test('logout static method logs out account', function (): void {
    $user1 = User::factory()->create(['username' => 'user1']);
    $user2 = User::factory()->create(['username' => 'user2']);

    Accounts::push($user1);
    Accounts::push($user2);
    $this->actingAs($user1);

    Accounts::logout($user1);

    expect(auth()->user()->username)->toBe('user2');
});

test('all generates remember token for current user if missing', function (): void {
    $user = User::factory()->create(['remember_token' => null]);
    $this->actingAs($user);

    $accounts = Accounts::all();

    expect($user->fresh()->remember_token)->not->toBeNull()
        ->and($accounts)->toHaveKey($user->username);
});

test('push accepts string username for existing user', function (): void {
    $user = User::factory()->create(['username' => 'stringuser']);

    Accounts::push('stringuser');

    $accounts = Accounts::all();
    expect($accounts)->toHaveKey('stringuser');
});

test('push silently ignores non-existent string username', function (): void {
    Accounts::push('doesnotexist');

    $accounts = Accounts::all();
    expect($accounts)->not->toHaveKey('doesnotexist');
});

test('all ignores malformed cookie items that are not key-array pairs', function (): void {
    $user = User::factory()->create(['username' => 'validuser']);
    $cookieData = json_encode([
        'validuser' => ['id' => $user->id, 'hash' => app(Accounts::class)->hashFor($user)],
        'bad_entry' => 'not-an-array',
        123 => ['id' => 999, 'hash' => 'hash'],
    ]);
    request()->cookies->set('accounts', $cookieData);

    $accounts = Accounts::all();

    expect($accounts)->toHaveKey('validuser')
        ->and($accounts)->not->toHaveKey('bad_entry');
});

test('logout with single account clears session and accounts cookie', function (): void {
    $user = User::factory()->create(['username' => 'singleuser']);

    Accounts::push($user);
    $this->actingAs($user);

    Accounts::logout($user);

    expect(auth()->check())->toBeFalse();

    $queuedCookies = cookie()->getQueuedCookies();
    $accountsCookie = collect($queuedCookies)->last(fn ($cookie): bool => $cookie->getName() === 'accounts');
    expect($accountsCookie)->not()->toBeNull()
        ->and($accountsCookie->getValue())->toBeNull();
});

test('remove last account forgets accounts cookie', function (): void {
    $user = User::factory()->create(['username' => 'user1']);

    Accounts::push($user);
    Accounts::remove('user1');

    $queuedCookies = cookie()->getQueuedCookies();
    $accountsCookie = collect($queuedCookies)->last(fn ($cookie): bool => $cookie->getName() === 'accounts');
    expect($accountsCookie)->not()->toBeNull()
        ->and($accountsCookie->getValue())->toBeNull();
});
