<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use JsonException;

/**
 * A short-lived, signed stand-in for the session the web holds mid-challenge.
 *
 * Fortify parks the challenged user id in the session between the two login
 * steps. A Sanctum token has no session, so the id travels here instead,
 * encrypted so it cannot be forged or pointed at another account.
 */
final readonly class TwoFactorChallenge
{
    /**
     * How long a challenge stays answerable.
     */
    public const int TTL_MINUTES = 5;

    /**
     * Cache key prefix for spent challenges.
     */
    private const string SPENT_KEY = 'auth.2fa.spent:';

    /**
     * Mint a challenge for a user who has passed the password check.
     */
    public function issue(User $user): string
    {
        return Crypt::encryptString(json_encode([
            'uid' => $user->getKey(),
            'iat' => now()->getTimestamp(),
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * Resolve a challenge back to its user, or null if it is not usable.
     *
     * A null return covers every rejection reason indistinguishably: tampered,
     * expired, already spent, or a user who has since turned 2FA off.
     */
    public function resolve(string $challenge): ?User
    {
        $payload = $this->decode($challenge);

        if ($payload === null) {
            return null;
        }

        $age = now()->getTimestamp() - $payload['iat'];

        if ($age < 0 || $age > self::TTL_MINUTES * 60) {
            return null;
        }

        $user = User::find($payload['uid']);

        if (! $user instanceof User || ! $user->hasEnabledTwoFactorAuthentication()) {
            return null;
        }

        return $user;
    }

    /**
     * Burn a challenge so one answer cannot mint a second token.
     *
     * A TOTP code stays valid for its whole window, so without this a client
     * that captured one challenge plus one code could replay both.
     */
    public function spend(string $challenge): void
    {
        Cache::put(self::SPENT_KEY.$this->fingerprint($challenge), true, self::TTL_MINUTES * 60);
    }

    /**
     * Whether a challenge has already been answered.
     */
    public function isSpent(string $challenge): bool
    {
        return Cache::has(self::SPENT_KEY.$this->fingerprint($challenge));
    }

    /**
     * Decrypt and validate a challenge's claims.
     *
     * @return array{uid: int, iat: int}|null
     */
    private function decode(string $challenge): ?array
    {
        if ($this->isSpent($challenge)) {
            return null;
        }

        try {
            $decoded = json_decode(Crypt::decryptString($challenge), true, 8, JSON_THROW_ON_ERROR);
        } catch (DecryptException|JsonException) {
            return null;
        }

        if (! is_array($decoded) || ! isset($decoded['uid'], $decoded['iat'])) {
            return null;
        }

        if (! is_int($decoded['uid']) || ! is_int($decoded['iat'])) {
            return null;
        }

        return ['uid' => $decoded['uid'], 'iat' => $decoded['iat']];
    }

    /**
     * A key for the spent-challenge cache that does not store the token.
     */
    private function fingerprint(string $challenge): string
    {
        return hash('sha256', $challenge);
    }
}
