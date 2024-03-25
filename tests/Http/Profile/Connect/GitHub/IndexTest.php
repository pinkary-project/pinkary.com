<?php

declare(strict_types=1);

use App\Models\User;

test('guest', function () {
    $response = $this->get(route('profile.connect.github'));

    $response->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('redirect to github', function () {
    $response = $this->actingAs(User::factory()->create())
        ->get(route('profile.connect.github'));

    $response->assertStatus(302);
    $response->assertRedirectContains('https://github.com/login/oauth/authorize');
});
