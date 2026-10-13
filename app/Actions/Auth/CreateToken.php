<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Carbon;

final readonly class CreateToken
{
    /** Create a personal access token for the given user. */
    public function handle(User $user, string $name = 'pinkary-mobile'): string
    {
        // Token pruning uses expires_at; Sanctum's age limit does not populate it.
        $expiration = config('sanctum.expiration');

        $expiresAt = is_numeric($expiration)
            ? Carbon::now()->addMinutes((int) $expiration)
            : null;

        return $user->createToken($name, ['*'], $expiresAt)->plainTextToken;
    }
}
