<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Accounts;

test('authenticated user can log out of all accounts', function (): void {
    $user1 = User::factory()->create(['username' => 'alice']);
    $user2 = User::factory()->create(['username' => 'bob']);

    $accounts = app(Accounts::class);
    $accounts->pushAccount($user1);
    $accounts->pushAccount($user2);

    $this->actingAs($user1);

    $response = $this->post(route('accounts.logout-all'));

    $response->assertRedirect(route('home.feed'));
    $this->assertGuest();

    $queuedCookies = cookie()->getQueuedCookies();
    $accountsCookie = collect($queuedCookies)->last(fn ($cookie): bool => $cookie->getName() === 'accounts');
    expect($accountsCookie)->not()->toBeNull()
        ->and($accountsCookie->getValue())->toBeNull();
});

test('guest cannot log out of all accounts', function (): void {
    $response = $this->post(route('accounts.logout-all'));

    $response->assertRedirect(route('login'));
});
