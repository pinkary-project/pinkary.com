<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;

final readonly class UpdateUserSettings
{
    /**
     * Update the user's link appearance settings.
     *
     * @param  array<string, mixed>  $settings
     */
    public function handle(User $user, array $settings): void
    {
        $user->update(['settings' => $settings]);
    }
}
