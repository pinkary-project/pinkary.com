<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Models\User;
use App\Services\Accounts;

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

    $accounts = app(Accounts::class);
    request()->cookies->set('accounts', json_encode([
        'john' => ['id' => $user1->id, 'hash' => $accounts->hashFor($user1)],
        'jane' => ['id' => $user2->id, 'hash' => $accounts->hashFor($user2)],
        'bob' => ['id' => $user3->id, 'hash' => $accounts->hashFor($user3)],
    ]));

    $controller = new AuthenticatedSessionController();

    $response = $controller->destroy($user1, $accounts);

    expect(auth()->user()->username)->toBe('bob')
        ->and($response)->toBeInstanceOf(Illuminate\Http\RedirectResponse::class);
});

test('destroy method performs full logout when only one account exists', function (): void {
    $user = User::factory()->create(['username' => 'john']);

    $this->actingAs($user);

    $accounts = app(Accounts::class);
    request()->cookies->set('accounts', json_encode([
        'john' => ['id' => $user->id, 'hash' => $accounts->hashFor($user)],
    ]));

    $controller = new AuthenticatedSessionController();

    $response = $controller->destroy($user, $accounts);

    expect(auth()->check())->toBeFalse()
        ->and($response)->toBeInstanceOf(Illuminate\Http\RedirectResponse::class);
});

test('destroy method performs full logout when no accounts exist', function (): void {
    $user = User::factory()->create(['username' => 'john']);

    $this->actingAs($user);

    request()->cookies->set('accounts', json_encode([]));

    $controller = new AuthenticatedSessionController();

    $response = $controller->destroy($user, app(Accounts::class));

    expect(auth()->check())->toBeFalse()
        ->and($response)->toBeInstanceOf(Illuminate\Http\RedirectResponse::class);
});

test('destroy method removes current user from accounts cookie', function (): void {
    $user1 = User::factory()->create(['username' => 'john']);
    $user2 = User::factory()->create(['username' => 'jane']);

    $this->actingAs($user1);

    $accounts = app(Accounts::class);
    request()->cookies->set('accounts', json_encode([
        'john' => ['id' => $user1->id, 'hash' => $accounts->hashFor($user1)],
        'jane' => ['id' => $user2->id, 'hash' => $accounts->hashFor($user2)],
    ]));

    $controller = new AuthenticatedSessionController();

    $controller->destroy($user1, $accounts);

    $queuedCookies = cookie()->getQueuedCookies();
    $accountsCookie = collect($queuedCookies)->last(fn ($cookie): bool => $cookie->getName() === 'accounts');

    expect($accountsCookie)->not()->toBeNull();

    $accountsData = json_decode($accountsCookie->getValue(), true);
    expect($accountsData)->not()->toHaveKey('john')
        ->and($accountsData)->toHaveKey('jane');
});

test('destroy method queues accounts cookie to be forgotten when no accounts remain', function (): void {
    $user = User::factory()->create(['username' => 'john']);

    $this->actingAs($user);

    $accounts = app(Accounts::class);
    request()->cookies->set('accounts', json_encode([
        'john' => ['id' => $user->id, 'hash' => $accounts->hashFor($user)],
    ]));

    $controller = new AuthenticatedSessionController();

    $controller->destroy($user, $accounts);

    $queuedCookies = cookie()->getQueuedCookies();
    $forgetCookie = collect($queuedCookies)->last(fn ($cookie): bool => $cookie->getName() === 'accounts' && $cookie->getValue() === null
    );

    expect($forgetCookie)->not()->toBeNull();
});
