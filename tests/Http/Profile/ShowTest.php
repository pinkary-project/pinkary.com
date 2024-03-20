<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('guest', function () {
    $response = $this->get(route('profile.show', ['user' => $this->user->username]));

    $response->assertSee($this->user->name);
});

test('auth', function () {
    $response = $this->get(route('profile.show', ['user' => $this->user->username]));

    $response->assertSee($this->user->name);
});
