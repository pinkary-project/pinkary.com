<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\getJson;

test('a guest can fetch the qr code without logging in', function (): void {
    $user = User::factory()->create();

    getJson(route('api.v1.users.qr-code', ['user' => $user->username]))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');
});

test('an invalid theme is rejected', function (): void {
    $user = User::factory()->create();

    getJson(route('api.v1.users.qr-code', ['theme' => 'neon', 'user' => $user->username]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('theme');
});

test('both dark and light themes render', function (): void {
    $user = User::factory()->create();

    foreach (['dark', 'light'] as $theme) {
        getJson(route('api.v1.users.qr-code', ['theme' => $theme, 'user' => $user->username]))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }
});
