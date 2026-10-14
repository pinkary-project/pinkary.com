<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;

final class MobileVerifyEmail extends VerifyEmail
{
    /** Create a signed verification URL that does not depend on a browser session. */
    protected function verificationUrl(mixed $notifiable): string
    {
        assert($notifiable instanceof User);

        $path = URL::temporarySignedRoute(
            'api.v1.account.email-verification.complete',
            now()->addMinutes(config()->integer('auth.verification.expire', 60)),
            [
                'user' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
            absolute: false,
        );

        return mb_rtrim(config()->string('app.url'), '/').$path;
    }
}
