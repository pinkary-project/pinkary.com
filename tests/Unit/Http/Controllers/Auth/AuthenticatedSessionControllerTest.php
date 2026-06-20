<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Models\User;

test('create method returns login view', function () {
    $controller = new AuthenticatedSessionController();

    $response = $controller->create();

    expect($response)->toBeInstanceOf(Illuminate\View\View::class);
    expect($response->getName())->toBe('auth.login');
});

test('destroy method switches to last account when multiple accounts exist', function () {
    $user1 = User::factory()->create(['username' => 'john']);
    $user2 = User::factory()->create(['username' => 'jane']);
    $user3 = User::factory()->create(['username' => 'bob']);

    $this->actingAs($user1);

    request()->cookies->set('accounts', json_encode([
        'john' => true,
        'jane' => true,
        'bob' => true,
    ]));

    $controller = new AuthenticatedSessionController();

    $response = $controller->destroy($user1);

    expect(auth()->user()->username)->toBe('bob');
    expect($response)->toBeInstanceOf(Illuminate\Http\RedirectResponse::class);
});

test('destroy method performs full logout when only one account exists', function () {
    $user = User::factory()->create(['username' => 'john']);

    $this->actingAs($user);

    request()->cookies->set('accounts', json_encode(['john' => true]));

    $controller = new AuthenticatedSessionController();

    $response = $controller->destroy($user);

    expect(auth()->check())->toBeFalse();
    expect($response)->toBeInstanceOf(Illuminate\Http\RedirectResponse::class);
});

test('destroy method performs full logout when no accounts exist', function () {
    $user = User::factory()->create(['username' => 'john']);

    $this->actingAs($user);

    request()->cookies->set('accounts', json_encode([]));

    $controller = new AuthenticatedSessionController();

    $response = $controller->destroy($user);

    expect(auth()->check())->toBeFalse();
    expect($response)->toBeInstanceOf(Illuminate\Http\RedirectResponse::class);
});

test('destroy method removes current user from accounts cookie', function () {
    $user1 = User::factory()->create(['username' => 'john']);
    $user2 = User::factory()->create(['username' => 'jane']);

    $this->actingAs($user1);

    request()->cookies->set('accounts', json_encode([
        'john' => true,
        'jane' => true,
    ]));

    $controller = new AuthenticatedSessionController();

    $controller->destroy($user1);

    $queuedCookies = cookie()->getQueuedCookies();
    $accountsCookie = collect($queuedCookies)->first(fn ($cookie) => $cookie->getName() === 'accounts');

    expect($accountsCookie)->not()->toBeNull();

    $accounts = json_decode($accountsCookie->getValue(), true);
    expect($accounts)->not()->toHaveKey('john');
    expect($accounts)->toHaveKey('jane');
});

test('destroy method queues accounts cookie to be forgotten when no accounts remain', function () {
    $user = User::factory()->create(['username' => 'john']);

    $this->actingAs($user);

    request()->cookies->set('accounts', json_encode(['john' => true]));

    $controller = new AuthenticatedSessionController();

    $controller->destroy($user);

    $queuedCookies = cookie()->getQueuedCookies();
    $forgetCookie = collect($queuedCookies)->first(fn ($cookie) => $cookie->getName() === 'accounts' && $cookie->getValue() === null
    );

    expect($forgetCookie)->not()->toBeNull();
});
