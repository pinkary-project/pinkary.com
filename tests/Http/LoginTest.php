<?php

declare(strict_types=1);

use App\Models\User;

test('guest', function (): void {
    $response = $this->get('/login');

    $response->assertOk()
        ->assertSee('Log In');
});

test('users can authenticate', function (): void {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();

    $response->assertRedirect(route('home.feed', absolute: false));
});

test('users are rate limited', function (): void {
    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(302)->assertSessionHasErrors([
            'email' => 'These credentials do not match our records.',
        ]);
    }

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertStatus(429);
});

test('users can not authenticate with invalid password', function (): void {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertStatus(302)->assertSessionHasErrors([
        'email' => 'These credentials do not match our records.',
    ]);

    $this->assertGuest();
});

test('authenticated user can add another account via login', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $this->actingAs($user1);

    $response = $this->post('/login', [
        'email' => $user2->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user2);
    $response->assertRedirect(route('home.feed', absolute: false));
    $response->assertCookie('accounts');
});

test('authenticated user can access two-factor-challenge when adding 2fa account', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create([
        'two_factor_secret' => 'secret',
        'two_factor_confirmed_at' => now(),
    ]);

    $this->actingAs($user1);

    $response = $this->post('/login', [
        'email' => $user2->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.login', absolute: false));

    $challengeResponse = $this->get(route('two-factor.login'));
    $challengeResponse->assertOk()
        ->assertViewIs('auth.two-factor-challenge');
});

test('authenticated user can complete two-factor challenge when adding 2fa account', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create([
        'two_factor_secret' => encrypt('secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
        'two_factor_confirmed_at' => now(),
    ]);

    $this->actingAs($user1);

    $response = $this->withSession(['login.id' => $user2->id])
        ->post(route('two-factor.login'), [
            'recovery_code' => 'recovery-code-1',
        ]);

    $this->assertAuthenticatedAs($user2);
    $response->assertRedirect(route('home.feed', absolute: false));
    $response->assertCookie('accounts');
});
