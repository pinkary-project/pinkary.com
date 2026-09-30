<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final readonly class AuthenticateUser
{
    /**
     * Authenticate a user by credentials.
     *
     * @throws ValidationException
     */
    public function handle(string $email, string $password): User
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user instanceof User || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // The mobile client has no screen to complete a 2FA challenge in, so
        // refuse rather than mint a token that bypasses the setting.
        if ($user->hasEnabledTwoFactorAuthentication()) {
            throw ValidationException::withMessages([
                'email' => ['Two-factor authentication is enabled for this account. Sign in on the website to continue.'],
            ]);
        }

        return $user;
    }
}
