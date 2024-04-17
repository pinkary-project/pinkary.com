<?php

declare(strict_types=1);

use App\Jobs\IncrementViews;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('guest', function () {
    $response = $this->get(route('profile.show', ['username' => $this->user->username]));

    $response->assertSee($this->user->name);
});

test('auth', function () {
    $response = $this->get(route('profile.show', ['username' => $this->user->username]));

    $response->assertSee($this->user->name);
});

it('can show profile on username case-insensitive', function () {
    $username = $this->user->username;
    $revertCasingUsername = mb_strtolower($username) ^ mb_strtoupper($username) ^ $username;
    $response = $this->get(route('profile.show', ['username' => $revertCasingUsername]));

    $response->assertSee($this->user->name);
});

it('does increment views', function () {
    Queue::fake(IncrementViews::class);
    $this->actingAs($this->user);

    $response = $this->get(route('profile.show', ['username' => $this->user->username]));

    $response->assertSee($this->user->name);
    Queue::assertPushed(IncrementViews::class);
});
