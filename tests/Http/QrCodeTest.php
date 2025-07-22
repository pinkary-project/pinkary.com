<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\QrCode;

test('can QR Code be downloaded only by authenticated users', function () {
    $response = $this->get(route('qr-code.image'));

    $response->assertRedirect(route('login'));
});

test('for dark or default theme', function () {
    $user = User::factory()->create();

    $qrCode = (new QrCode(lightMode: false))->generate(
        route('profile.show', [
            'username' => $user->username,
        ])
    );

    $response = $this->actingAs($user)->get(route('qr-code.image'));

    $response
        ->assertOk()
        ->assertStreamedContent($qrCode->toHtml())
        ->assertHeader('content-type', 'image/png')
        ->assertDownload('pinkary_'.$user->username.'.png');
});

test('for light theme', function () {
    $user = User::factory()->create();

    $qrCode = (new QrCode(lightMode: true))->generate(
        route('profile.show', [
            'username' => $user->username,
        ])
    );

    $response = $this->actingAs($user)->get(route('qr-code.image', ['theme' => 'light']));

    $response
        ->assertOk()
        ->assertStreamedContent($qrCode->toHtml())
        ->assertHeader('content-type', 'image/png')
        ->assertDownload('pinkary_'.$user->username.'.png');
});
