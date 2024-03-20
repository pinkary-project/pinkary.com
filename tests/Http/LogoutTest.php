<?php

declare(strict_types=1);

use App\Models\User;

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();

    $response->assertRedirect('/');
});

test('users can only logout when authenticated', function () {
    $this->assertGuest();

    $response = $this->post('/logout');

    $this->assertGuest();

    $response->assertRedirect('/login');
});
