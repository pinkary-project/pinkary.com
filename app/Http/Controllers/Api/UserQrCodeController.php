<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Users\GenerateUserQrCode;
use App\Http\Requests\Api\UserQrCodeRequest;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class UserQrCodeController
{
    /**
     * Stream the user's public profile QR code (same artwork as the web
     * modal) so image-only clients can render it without session auth.
     */
    public function __invoke(UserQrCodeRequest $request, User $user, GenerateUserQrCode $generateUserQrCode): StreamedResponse
    {
        $qrCode = $generateUserQrCode->handle(
            $user,
            $request->validated('theme', 'dark') === 'light',
        );

        return response()->streamDownload(
            function () use ($qrCode): void {
                echo $qrCode->toHtml();
            },
            'pinkary_'.$user->username.'.png',
            ['Content-Type' => 'image/png']
        );
    }
}
