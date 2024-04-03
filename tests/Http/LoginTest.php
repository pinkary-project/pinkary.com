<?php

declare(strict_types=1);

use App\Models\User;

test('guest', function () {
    $response = $this->get('/login');

    $response->assertOk()
        ->assertSee('Log In');
});

test('users can authenticate using email', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'username' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();

    $response->assertRedirect(route('profile.show', [
        'username' => $user->username,
    ], absolute: false));
});

test('users can authenticate using username', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();

    $response->assertRedirect(route('profile.show', [
        'username' => $user->username,
    ], absolute: false));
});

test('users are rate limited', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'username' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(302)->assertSessionHasErrors([
            'username' => 'These credentials do not match our records.',
        ]);
    }

    $this->post('/login', [
        'username' => $user->email,
        'password' => 'wrong-password',
    ])->assertStatus(302)->assertSessionHasErrors([
        'email',
    ]);
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'username' => $user->email,
        'password' => 'wrong-password',
    ])->assertStatus(302)->assertSessionHasErrors([
        'username' => 'These credentials do not match our records.',
    ]);

    $this->assertGuest();
});
