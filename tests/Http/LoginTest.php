<?php

declare(strict_types=1);

use App\Models\User;

test('guest', function () {
    $response = $this->get('/login');

    $response->assertOk()
        ->assertSee('Log In');
});

test('users can authenticate', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();

    $response->assertRedirect(route('home.feed', absolute: false));
});

test('users can authenticate with username', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();

    $response->assertRedirect(route('home.feed', absolute: false));
});

test('user can not authenticate with unverified email', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();

    $response->assertSessionHasErrors([
        'email' => 'These credentials do not match our records.',
    ]);
});

test('users are rate limited', function () {
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

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertStatus(302)->assertSessionHasErrors([
        'email' => 'These credentials do not match our records.',
    ]);

    $this->assertGuest();
});
