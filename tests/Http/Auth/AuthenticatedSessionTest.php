<?php

declare(strict_types=1);

use App\Models\User;

test('login page can be rendered', function () {
    $response = $this->get('/login');

    $response->assertOk()
        ->assertViewIs('auth.login');
});

test('logout with multiple accounts switches to last account', function () {
    $user1 = User::factory()->create(['username' => 'john']);
    $user2 = User::factory()->create(['username' => 'jane']);
    $user3 = User::factory()->create(['username' => 'bob']);

    $this->actingAs($user1);

    $response = $this->withCookies([
        'accounts' => json_encode([
            'john' => true,
            'jane' => true,
            'bob' => true,
        ]),
    ])->post('/logout');

    expect(auth()->user()->username)->toBe('bob');
    $response->assertRedirect();
});

test('logout with single account performs full logout', function () {
    $user = User::factory()->create(['username' => 'john']);

    $this->actingAs($user);

    $response = $this->withCookies([
        'accounts' => json_encode(['john' => true]),
    ])->post('/logout');

    $this->assertGuest();
    $response->assertRedirect();
});

test('logout with no accounts performs full logout', function () {
    $user = User::factory()->create(['username' => 'john']);

    $this->actingAs($user);

    $response = $this->post('/logout');

    $this->assertGuest();
    $response->assertRedirect();
});

test('logout removes current user from accounts', function () {
    $user1 = User::factory()->create(['username' => 'john']);
    $user2 = User::factory()->create(['username' => 'jane']);

    $this->actingAs($user1);

    $response = $this->withCookies([
        'accounts' => json_encode([
            'john' => true,
            'jane' => true,
        ]),
    ])->post('/logout');

    expect(auth()->user()->username)->toBe('jane');
    $response->assertRedirect();
});
