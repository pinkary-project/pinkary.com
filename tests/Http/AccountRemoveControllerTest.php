<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Accounts;

test('authenticated user can remove an inactive account from their accounts list', function (): void {
    $user1 = User::factory()->create(['username' => 'alice']);
    $user2 = User::factory()->create(['username' => 'bob']);

    $accounts = app(Accounts::class);
    $accounts->pushAccount($user1);
    $accounts->pushAccount($user2);

    $this->actingAs($user1);

    $response = $this->from('/profile')
        ->post(route('accounts.remove', 'bob'));

    $response->assertRedirect('/profile');
    expect(auth()->id())->toBe($user1->id);

    $queuedCookies = cookie()->getQueuedCookies();
    $accountsCookie = collect($queuedCookies)->last(fn ($cookie): bool => $cookie->getName() === 'accounts');
    expect($accountsCookie)->not()->toBeNull();

    $accountsList = json_decode((string) $accountsCookie->getValue(), true);
    expect($accountsList)->toHaveKey('alice')
        ->and($accountsList)->not->toHaveKey('bob');
});

test('guest cannot remove accounts', function (): void {
    $user = User::factory()->create(['username' => 'alice']);

    $response = $this->post(route('accounts.remove', $user->username));

    $response->assertRedirect(route('login'));
});

test('page renders account removal button and confirmation modal for inactive accounts', function (): void {
    $user1 = User::factory()->create(['username' => 'alice']);
    $user2 = User::factory()->create(['username' => 'bob']);

    $accounts = app(Accounts::class);
    $accounts->pushAccount($user1);
    $accounts->pushAccount($user2);

    $this->actingAs($user1);

    $response = $this->get(route('profile.edit'));

    $response->assertOk();
    $response->assertSee('confirm-remove-account');
    $response->assertSee('Log out of account?');
});
