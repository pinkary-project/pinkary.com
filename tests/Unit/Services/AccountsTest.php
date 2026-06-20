<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Accounts;

beforeEach(function () {
    cookie()->queue(cookie()->forget('accounts'));
});

test('all returns empty array when no accounts cookie exists', function () {
    $accounts = Accounts::all();

    expect($accounts)->toBe([]);
});

test('all returns accounts from cookie', function () {
    $cookieData = json_encode(['john' => true, 'jane' => true]);
    request()->cookies->set('accounts', $cookieData);

    $accounts = Accounts::all();

    expect($accounts)->toBe(['john' => true, 'jane' => true]);
});

test('all handles invalid json in cookie', function () {
    request()->cookies->set('accounts', 'invalid-json');

    $accounts = Accounts::all();

    expect($accounts)->toBe([]);
});

test('push adds account to cookie', function () {
    Accounts::push('testuser');

    $queuedCookies = cookie()->getQueuedCookies();
    $accountsCookie = collect($queuedCookies)->first(fn ($cookie) => $cookie->getName() === 'accounts');

    expect($accountsCookie)->not()->toBeNull();

    $accounts = json_decode($accountsCookie->getValue(), true);
    expect($accounts)->toBe(['testuser' => true]);
});

test('push adds multiple accounts to cookie', function () {
    Accounts::push('user1');

    request()->cookies->set('accounts', json_encode(['user1' => true]));

    Accounts::push('user2');

    $queuedCookies = cookie()->getQueuedCookies();
    $accountsCookie = collect($queuedCookies)->last(fn ($cookie) => $cookie->getName() === 'accounts');

    $accounts = json_decode($accountsCookie->getValue(), true);
    expect($accounts)->toBe(['user1' => true, 'user2' => true]);
});

test('switch authenticates user when account exists', function () {
    $user = User::factory()->create(['username' => 'testuser']);

    Accounts::push('testuser');
    request()->cookies->set('accounts', json_encode(['testuser' => true]));

    Accounts::switch('testuser');

    expect(auth()->check())->toBeTrue();
    expect(auth()->user()->username)->toBe('testuser');
});

test('switch throws exception when account not found in cookie', function () {
    $user = User::factory()->create(['username' => 'testuser']);

    expect(fn () => Accounts::switch('testuser'))
        ->toThrow('Unauthorized action.');
});

test('switch throws exception when user not found in database', function () {
    request()->cookies->set('accounts', json_encode(['nonexistent' => true]));

    expect(fn () => Accounts::switch('nonexistent'))
        ->toThrow('User not found.');
});

test('remove deletes account from cookie', function () {
    request()->cookies->set('accounts', json_encode(['user1' => true, 'user2' => true]));

    Accounts::remove('user1');

    $queuedCookies = cookie()->getQueuedCookies();
    $accountsCookie = collect($queuedCookies)->first(fn ($cookie) => $cookie->getName() === 'accounts');

    $accounts = json_decode($accountsCookie->getValue(), true);
    expect($accounts)->toBe(['user2' => true]);
});

test('remove handles non-existent account gracefully', function () {
    request()->cookies->set('accounts', json_encode(['user1' => true]));

    Accounts::remove('nonexistent');

    $queuedCookies = cookie()->getQueuedCookies();
    $accountsCookie = collect($queuedCookies)->first(fn ($cookie) => $cookie->getName() === 'accounts');

    $accounts = json_decode($accountsCookie->getValue(), true);
    expect($accounts)->toBe(['user1' => true]);
});
