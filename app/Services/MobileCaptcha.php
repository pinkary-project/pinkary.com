<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

final readonly class MobileCaptcha
{
    /** Check whether an action requires captcha. */
    public function required(string $action, ?User $user): bool
    {
        if ($action === 'register') {
            return app()->environment(['production', 'testing']);
        }

        return app()->isProduction() && $user instanceof User && $user->followers()->doesntExist();
    }
}
