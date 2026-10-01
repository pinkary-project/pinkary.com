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

/**
 * Check a two factor answer against Fortify's own verification.
 *
 * Deliberately reuses the provider and the recovery-code storage rather than
 * reimplementing either, so the API cannot drift from what the web accepts.
 */
final readonly class VerifyTwoFactorCode
{
    /**
     * Create a new action instance.
     */
    public function __construct(
        private TwoFactorAuthenticationProvider $provider,
    ) {}

    /**
     * Verify a one-time password or a recovery code.
     *
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

    /**
     * Whether a one-time password is currently valid for the user.
     */
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
            // A stored secret that is not valid base32 cannot be checked, and
            // throwing here would turn a bad code into a 500. Recovery is the
            // account owner's: re-enrol 2FA on the web.
            return false;
        }
    }

    /**
     * Whether a recovery code matches, consuming it so it cannot be reused.
     */
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
