<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('guest', function () {
    $response = $this->get(route('profile.show', ['user' => $this->user]));

    $response->assertSee($this->user->name);
});

test('auth', function () {
    $response = $this->get(route('profile.show', ['user' => $this->user->user]));

    $response->assertSee($this->user->name);
});

it('can show profile on username case-insensitive', function () {
    $username = $this->user->username;
    $revertCasingUsername = mb_strtolower($username) ^ mb_strtoupper($username) ^ $username;
    $this->user->update(['username' => $revertCasingUsername]);
    $response = $this->get(route('profile.show', ['username' => $this->user->username]));

    $response->assertSee($this->user->name);
});
