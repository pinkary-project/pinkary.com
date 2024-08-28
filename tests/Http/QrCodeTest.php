<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\QrCode;

test('can QR Code be downloaded only by authenticated users', function () {
    $response = $this->get(route('qr-code.image'));

    $response->assertRedirect(route('login'));
});

test('user can download qr code', function () {
    $user = User::factory()->create();

    $qrCode = (new QrCode())->generate(
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
