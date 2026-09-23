<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;

final readonly class CreateToken
{
    /**
     * Create a personal access token for the given user.
     */
    public function handle(User $user, string $name = 'pinkary-mobile'): string
    {
        return $user->createToken($name)->plainTextToken;
    }
}
