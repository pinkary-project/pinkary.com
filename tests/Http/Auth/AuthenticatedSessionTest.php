<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Accounts;

test('login page can be rendered', function (): void {
    $response = $this->get('/login');

    $response->assertOk()
        ->assertViewIs('auth.login');
});

test('logout with multiple accounts switches to last account', function (): void {
    $user1 = User::factory()->create(['username' => 'john']);
    $user2 = User::factory()->create(['username' => 'jane']);
    $user3 = User::factory()->create(['username' => 'bob']);

    $this->actingAs($user1);

    $accounts = app(Accounts::class);
    $response = $this->withCookies([
        'accounts' => json_encode([
            'john' => ['id' => $user1->id, 'hash' => $accounts->hashFor($user1)],
            'jane' => ['id' => $user2->id, 'hash' => $accounts->hashFor($user2)],
            'bob' => ['id' => $user3->id, 'hash' => $accounts->hashFor($user3)],
        ]),
    ])->post('/logout');

    expect(auth()->user()->username)->toBe('bob');
    $response->assertRedirect();
});

test('logout with single account performs full logout', function (): void {
    $user = User::factory()->create(['username' => 'john']);

    $this->actingAs($user);

    $accounts = app(Accounts::class);
    $response = $this->withCookies([
        'accounts' => json_encode([
            'john' => ['id' => $user->id, 'hash' => $accounts->hashFor($user)],
        ]),
    ])->post('/logout');

    $this->assertGuest();
    $response->assertRedirect();
});

test('logout with no accounts performs full logout', function (): void {
    $user = User::factory()->create(['username' => 'john']);

    $this->actingAs($user);

    $response = $this->post('/logout');

    $this->assertGuest();
    $response->assertRedirect();
});

test('logout removes current user from accounts', function (): void {
    $user1 = User::factory()->create(['username' => 'john']);
    $user2 = User::factory()->create(['username' => 'jane']);

    $this->actingAs($user1);

    $accounts = app(Accounts::class);
    $response = $this->withCookies([
        'accounts' => json_encode([
            'john' => ['id' => $user1->id, 'hash' => $accounts->hashFor($user1)],
            'jane' => ['id' => $user2->id, 'hash' => $accounts->hashFor($user2)],
        ]),
    ])->post('/logout');

    expect(auth()->user()->username)->toBe('jane');
    $response->assertRedirect();
});
