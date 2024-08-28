<?php

declare(strict_types=1);

use App\Models\User;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Writer;
use Imagick;

test('can QR Code be downloaded only by authenticated users', function () {
    $response = $this->get(route('qr-code.image'));

    $response->assertRedirect(route('login'));
});

test('user can download qr code', function () {
    $user = User::factory()->create();

    // Generate the QR code using the same logic as in the controller
    $backgroundColor = new Rgb(3, 7, 18);
    $foregroundColor = new Rgb(236, 72, 153);

    $base64QrCode = (new Writer(
        renderer: new ImageRenderer(
            rendererStyle: new RendererStyle(size: 512, margin: 0, fill: Fill::withForegroundColor($backgroundColor, $foregroundColor, EyeFill::inherit(), EyeFill::inherit(), EyeFill::inherit())),
            imageBackEnd: new ImagickImageBackEnd()
        )
    ))->writeString(
        content: route('profile.show', [
            'username' => $user->username,
        ]),
        encoding: Encoder::DEFAULT_BYTE_MODE_ECODING,
        ecLevel: ErrorCorrectionLevel::M()
    );

    // Load the QR code image
    $qrCodeImage = new Imagick();
    $qrCodeImage->readImageBlob($base64QrCode);

    // Load the icon
    $icon = new Imagick(public_path('img/ico.png'));

    $iconSize = 100;
    $icon->resizeImage($iconSize, $iconSize, Imagick::FILTER_LANCZOS, 1);

    // Calculate the position to place the icon
    $x = ($qrCodeImage->getImageWidth() - $iconSize) / 2;
    $y = ($qrCodeImage->getImageHeight() - $iconSize) / 2;

    // Overlay the icon onto the QR code
    $qrCodeImage->compositeImage($icon, Imagick::COMPOSITE_OVER, (int)$x, (int)$y);

    $qrCode = $qrCodeImage->getImageBlob();

    // Simulate downloading the QR code
    $response = $this->actingAs($user)->get(route('qr-code.image'));

    $response
        ->assertOk()
        ->assertHeader('content-type', 'image/png')
        ->assertDownload('pinkary_' . $user->username . '.png')
        ->assertStreamedContent($qrCode);
});
