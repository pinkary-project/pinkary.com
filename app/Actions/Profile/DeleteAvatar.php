<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use Illuminate\Support\Facades\Storage;

final readonly class DeleteAvatar
{
    /**
     * Delete the avatar for the given user.
     */
    public static function execute(string $avatar): void
    {
        if (! Storage::disk('public')->exists(str_replace('storage/', '', $avatar))) {
            return;
        }

        Storage::disk('public')->delete(str_replace('storage/', '', $avatar));
    }
}
