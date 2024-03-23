<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class QrCodeController
{
    public function __invoke(Request $request): StreamedResponse
    {
        $user = $request->user();

        assert($user instanceof User);

        $qrCode = QrCode::size(512)
            ->format('png')
            ->backgroundColor(3, 7, 18)
            ->color(249, 168, 212)
            ->merge('/public/img/ico.png')
            ->errorCorrection('M')
            ->generate(route('profile.show', $user));

        return response()->streamDownload(
            function () use ($qrCode): void {
                /** @var string $qrCode */
                echo $qrCode;
            },
            'qr-code.png',
            ['Content-Type' => 'image/png']);
    }
}
