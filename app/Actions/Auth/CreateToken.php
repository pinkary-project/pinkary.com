<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Carbon;

final readonly class CreateToken
{
    /**
     * Create a personal access token for the given user.
     */
    public function handle(User $user, string $name = 'pinkary-mobile'): string
    {
        // Enforced at auth time against created_at, but sanctum:prune-expired
        // sweeps expires_at, so it has to be set here or nothing is pruned.
        $expiration = config('sanctum.expiration');

        $expiresAt = is_numeric($expiration)
            ? Carbon::now()->addMinutes((int) $expiration)
            : null;

        return $user->createToken($name, ['*'], $expiresAt)->plainTextToken;
    }
}
