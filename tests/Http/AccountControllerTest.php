<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Accounts;

test('authenticated user can switch to another account in their accounts list', function (): void {
    $user1 = User::factory()->create(['username' => 'alice']);
    $user2 = User::factory()->create(['username' => 'bob']);

    $accounts = app(Accounts::class);
    $accounts->pushAccount($user1);
    $accounts->pushAccount($user2);

    $this->actingAs($user1);

    $response = $this->from('/home')
        ->post(route('accounts.switch', 'bob'));

    $response->assertRedirect('/home');
    expect(auth()->id())->toBe($user2->id);
});

test('switching to an account not in accounts list throws 403', function (): void {
    $user1 = User::factory()->create(['username' => 'alice']);
    User::factory()->create(['username' => 'charlie']);

    $accounts = app(Accounts::class);
    $accounts->pushAccount($user1);

    $this->actingAs($user1);

    $response = $this->post(route('accounts.switch', 'charlie'));

    $response->assertForbidden();
    expect(auth()->id())->toBe($user1->id);
});

test('guest cannot switch accounts', function (): void {
    $user = User::factory()->create(['username' => 'alice']);

    $response = $this->post(route('accounts.switch', $user->username));

    $response->assertRedirect(route('login'));
});
