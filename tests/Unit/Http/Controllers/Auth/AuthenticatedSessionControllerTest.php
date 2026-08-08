<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Models\User;

test('create method returns login view', function (): void {
    $controller = new AuthenticatedSessionController();

    $response = $controller->create();

    expect($response)->toBeInstanceOf(Illuminate\View\View::class)
        ->and($response->getName())->toBe('auth.login');
});

test('destroy method switches to last account when multiple accounts exist', function (): void {
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

    expect(auth()->user()->username)->toBe('bob')
        ->and($response)->toBeInstanceOf(Illuminate\Http\RedirectResponse::class);
});

test('destroy method performs full logout when only one account exists', function (): void {
    $user = User::factory()->create(['username' => 'john']);

    $this->actingAs($user);

    request()->cookies->set('accounts', json_encode(['john' => true]));

    $controller = new AuthenticatedSessionController();

    $response = $controller->destroy($user);

    expect(auth()->check())->toBeFalse()
        ->and($response)->toBeInstanceOf(Illuminate\Http\RedirectResponse::class);
});

test('destroy method performs full logout when no accounts exist', function (): void {
    $user = User::factory()->create(['username' => 'john']);

    $this->actingAs($user);

    request()->cookies->set('accounts', json_encode([]));

    $controller = new AuthenticatedSessionController();

    $response = $controller->destroy($user);

    expect(auth()->check())->toBeFalse()
        ->and($response)->toBeInstanceOf(Illuminate\Http\RedirectResponse::class);
});

test('destroy method removes current user from accounts cookie', function (): void {
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
    $accountsCookie = collect($queuedCookies)->first(fn ($cookie): bool => $cookie->getName() === 'accounts');

    expect($accountsCookie)->not()->toBeNull();

    $accounts = json_decode($accountsCookie->getValue(), true);
    expect($accounts)->not()->toHaveKeys(['john', 'jane']);
});

test('destroy method queues accounts cookie to be forgotten when no accounts remain', function (): void {
    $user = User::factory()->create(['username' => 'john']);

    $this->actingAs($user);

    request()->cookies->set('accounts', json_encode(['john' => true]));

    $controller = new AuthenticatedSessionController();

    $controller->destroy($user);

    $queuedCookies = cookie()->getQueuedCookies();
    $forgetCookie = collect($queuedCookies)->first(fn ($cookie): bool => $cookie->getName() === 'accounts' && $cookie->getValue() === null
    );

    expect($forgetCookie)->not()->toBeNull();
});
