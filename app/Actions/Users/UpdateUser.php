<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Jobs\UpdateUserAvatar;
use App\Models\User;

final readonly class UpdateUser
{
    /**
     * Update the user's profile information.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $user, array $attributes): void
    {
        $user->fill($attributes);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($user->wasChanged('email')) {
            $user->sendEmailVerificationNotification();

            if (! $user->is_uploaded_avatar) {
                UpdateUserAvatar::dispatchFor($user);
            }
        }
    }
}
