<?php

declare(strict_types=1);

use App\Models\User;

test('a guest can fetch the qr code without logging in', function (): void {
    $user = User::factory()->create();

    $this->getJson(route('api.v1.users.qr-code', ['user' => $user->username]))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');
});

test('an invalid theme is rejected', function (): void {
    $user = User::factory()->create();

    $this->getJson(route('api.v1.users.qr-code', ['theme' => 'neon', 'user' => $user->username]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('theme');
});

test('both dark and light themes render', function (): void {
    $user = User::factory()->create();

    foreach (['dark', 'light'] as $theme) {
        $response = $this->getJson(route('api.v1.users.qr-code', ['theme' => $theme, 'user' => $user->username]))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');

        // A streamed response has no body until it is consumed, so asserting
        // on the headers alone never proves the callback ran. The PNG
        // signature also pins down that a real image came back, not an
        // empty or errored stream.
        expect($response->streamedContent())->toStartWith("\x89PNG");
    }
});
