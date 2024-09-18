<?php

declare(strict_types=1);

use App\Models\User;

test('can QR Code be downloaded only by authenticated users', function () {
    $response = $this->get(route('qr-code.image'));

    $response->assertRedirect(route('login'));
});

describe('user can download qr code', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();

        $this->qrCodeObj = QrCode::size(512)
            ->margin(2)
            ->format('png')
            ->color(236, 72, 153, 100)
            ->merge('/public/img/ico.png')
            ->errorCorrection('M');
    });

    test('for dark or default theme', function () {
        $user = $this->user;

        $qrCode = $this->qrCodeObj
            ->backgroundColor(3, 7, 18, 100)
            ->generate(route('profile.show', [
                'username' => $user->username,
            ]));

        $response = $this->actingAs($user)->get(route('qr-code.image'));

        $response
            ->assertOk()
            ->assertStreamedContent($qrCode->toHtml())
            ->assertHeader('content-type', 'image/png')
            ->assertDownload('pinkary_'.$user->username.'.png');
    });

    test('for light theme', function () {
        $user = $this->user;

        $qrCode = $this->qrCodeObj
            ->backgroundColor(248, 250, 252, 100)
            ->generate(route('profile.show', [
                'username' => $user->username,
            ]));

        $response = $this->actingAs($user)->get(route('qr-code.image', ['theme' => 'light']));

        $response
            ->assertOk()
            ->assertStreamedContent($qrCode->toHtml())
            ->assertHeader('content-type', 'image/png')
            ->assertDownload('pinkary_'.$user->username.'.png');
    });
});
