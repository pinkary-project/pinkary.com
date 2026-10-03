<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Events\RecoveryCodeReplaced;
use Laravel\Fortify\Fortify;
use Throwable;

final readonly class VerifyTwoFactorCode
{
    /** Configure two-factor verification. */
    public function __construct(
        private TwoFactorAuthenticationProvider $provider,
    ) {}

    /**
     * @throws ValidationException
     */
    public function handle(User $user, ?string $code, ?string $recoveryCode): void
    {
        if ($this->validCode($user, $code) || $this->validRecoveryCode($user, $recoveryCode)) {
            return;
        }

        throw ValidationException::withMessages([
            'code' => ['The provided two factor authentication code was invalid.'],
        ]);
    }

    /** Verify a one-time password. */
    private function validCode(User $user, ?string $code): bool
    {
        if ($code === null || $code === '' || ! is_string($user->two_factor_secret)) {
            return false;
        }

        try {
            $secret = Fortify::currentEncrypter()->decrypt($user->two_factor_secret);
        } catch (DecryptException) {
            return false;
        }

        if (! is_string($secret)) {
            return false;
        }

        try {
            return (bool) $this->provider->verify($secret, $code);
        } catch (Throwable) {
            // Invalid stored secrets must fail validation, not expose account state through a 500.
            return false;
        }
    }

    /** Verify and consume a recovery code. */
    private function validRecoveryCode(User $user, ?string $recoveryCode): bool
    {
        if ($recoveryCode === null || $recoveryCode === '') {
            return false;
        }

        $stored = $user->recoveryCodes();

        foreach ($stored as $candidate) {
            if (is_string($candidate) && hash_equals($candidate, $recoveryCode)) {
                $user->replaceRecoveryCode($candidate);

                RecoveryCodeReplaced::dispatch($user, $recoveryCode);

                return true;
            }
        }

        return false;
    }
}
