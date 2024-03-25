<?php

declare(strict_types=1);

use App\Models\User;

test('guest', function () {
    $response = $this->delete(route('profile.connect.github.destroy'));

    $response->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('disconnect github', function () {
    $user = User::factory()->create([
        'github_username' => 'test',
        'is_verified' => false,
    ]);

    $response = $this->actingAs($user)->delete(route('profile.connect.github.destroy'));

    $response->assertStatus(302);
    $response->assertRedirect(route('profile.edit'));

    expect(session('flash-message'))->toBe('Your GitHub account has been disconnected.');

    $user->refresh();

    expect($user->github_username)->toBeNull();
    expect($user->is_verified)->toBeFalse();
});

test('disconnect github and updates verified', function () {
    $user = User::factory()->create([
        'github_username' => 'test',
        'is_verified' => true,
    ]);

    $response = $this->actingAs($user)->delete(route('profile.connect.github.destroy'));

    $response->assertStatus(302);
    $response->assertRedirect(route('profile.edit'));

    expect(session('flash-message'))->toBe('Your GitHub account has been disconnected.');

    $user->refresh();

    expect($user->github_username)->toBeNull();
    expect($user->is_verified)->toBeFalse();
});
