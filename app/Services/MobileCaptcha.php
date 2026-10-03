<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

final readonly class MobileCaptcha
{
    /** Determine whether the mobile action requires a challenge. */
    public function required(string $action, ?User $user): bool
    {
        if ($action === 'register') {
            return app()->environment(['production', 'testing']);
        }

        return app()->isProduction() && $user instanceof User && $user->followers()->doesntExist();
    }
}
