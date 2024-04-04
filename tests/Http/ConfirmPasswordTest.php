<?php

declare(strict_types=1);

use App\Models\User;

test('only authenticated users can confirm their password', function () {
    $response = $this->get('/confirm-password');

    $response->assertRedirect('/login');
});

test('confirm password screen can be rendered', function () {
    $user = User::factory()->create();

    $this->withoutExceptionHandling();

    $response = $this->actingAs($user)->get('/confirm-password');

    $response->assertOk();
});

test('password can be confirmed', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/confirm-password', [
        'password' => 'password',
    ]);

    $response
        ->assertRedirect()
        ->assertSessionHasNoErrors();
});

test('password is not confirmed with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/confirm-password', [
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors();
});
