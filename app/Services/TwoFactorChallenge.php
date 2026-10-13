<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use JsonException;

/** Encrypted, short-lived user identity for sessionless two-factor sign-in. */
final readonly class TwoFactorChallenge
{
    public const int TTL_MINUTES = 5;

    private const string SPENT_KEY = 'auth.2fa.spent:';

    /** Mint a challenge for a user who has passed the password check. */
    public function issue(User $user): string
    {
        return Crypt::encryptString(json_encode([
            'uid' => $user->getKey(),
            'iat' => now()->getTimestamp(),
        ], JSON_THROW_ON_ERROR));
    }

    /** Resolve a valid challenge to its user. */
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

    /** Spending a challenge prevents replay while its TOTP code remains valid. */
    public function spend(string $challenge): void
    {
        Cache::put(self::SPENT_KEY.$this->fingerprint($challenge), true, self::TTL_MINUTES * 60);
    }

    /** Whether a challenge has already been answered. */
    public function isSpent(string $challenge): bool
    {
        return Cache::has(self::SPENT_KEY.$this->fingerprint($challenge));
    }

    /**
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

    /** Hash a challenge for cache keys. */
    private function fingerprint(string $challenge): string
    {
        return hash('sha256', $challenge);
    }
}
