<?php

declare(strict_types=1);

use App\Models\User;
use function Pest\Laravel\get;

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

it('can show profile on username case-insensitive', function () {
    $username = $this->user->username;
    $revertCasingUsername = strtolower($username) ^ strtoupper($username) ^ $username;
    $response = get(route('profile.show', ['user' => $revertCasingUsername]));

    $response->assertSee($this->user->name);
});
