<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

final readonly class Accounts
{
    /**
     * Get all authenticated accounts for the current browser session.
     *
     * @return array<string, User> Keyed by username
     */
    public static function all(): array
    {
        return app(self::class)->getAll();
    }

    /**
     * Push a user to the authenticated accounts list.
     */
    public static function push(User|string $user): void
    {
        app(self::class)->pushAccount($user);
    }

    /**
     * Switch the active authentication to the given username.
     */
    public static function switch(string $username): void
    {
        app(self::class)->switchAccount($username);
    }

    /**
     * Remove an account from the authenticated accounts list.
     */
    public static function remove(string $username): void
    {
        app(self::class)->removeAccount($username);
    }

    /**
     * Handle logging out the given user.
     * Switches to remaining account if available, otherwise performs full logout.
     */
    public static function logout(User $user): void
    {
        app(self::class)->logoutAccount($user);
    }

    /**
     * Get all authenticated accounts for the current browser session.
     *
     * @return array<string, User> Keyed by username
     */
    public function getAll(): array
    {
        $raw = $this->getRawPayload();
        $updated = false;

        if (Auth::check()) {
            /** @var User $currentUser */
            $currentUser = Auth::user();

            if (empty($currentUser->remember_token)) {
                $currentUser->forceFill(['remember_token' => Str::random(60)])->saveQuietly();
            }

            $expectedHash = $this->hashFor($currentUser);

            if (! isset($raw[$currentUser->username]) || $raw[$currentUser->username]['hash'] !== $expectedHash) {
                $raw[$currentUser->username] = [
                    'id' => $currentUser->id,
                    'hash' => $expectedHash,
                ];
                $updated = true;
            }
        }

        if ($raw === []) {
            return [];
        }

        $userIds = array_values(array_map(
            fn (array $item): int => $item['id'],
            $raw
        ));

        $users = User::query()
            ->whereIn('id', $userIds)
            ->get()
            ->keyBy('id');

        /** @var array<string, User> $validAccounts */
        $validAccounts = [];

        foreach ($raw as $oldUsername => $entry) {
            /** @var User|null $user */
            $user = $users->get($entry['id']);

            if ($user === null || empty($user->remember_token) || ! hash_equals($entry['hash'], $this->hashFor($user))) {
                $updated = true;

                continue;
            }

            if ($user->username !== $oldUsername) {
                $updated = true;
            }

            $validAccounts[$user->username] = $user;
        }

        if ($updated) {
            $newRaw = [];
            foreach ($validAccounts as $username => $user) {
                $newRaw[$username] = [
                    'id' => $user->id,
                    'hash' => $this->hashFor($user),
                ];
            }
            $this->saveRawPayload($newRaw);
        }

        return $validAccounts;
    }

    /**
     * Push an account to the accounts cookie.
     */
    public function pushAccount(User|string $user): void
    {
        if (is_string($user)) {
            $userModel = User::where('username', $user)->first();
            if ($userModel === null) {
                return;
            }
            $user = $userModel;
        }

        if (empty($user->remember_token)) {
            $user->forceFill(['remember_token' => Str::random(60)])->saveQuietly();
        }

        $raw = $this->getRawPayload();

        $raw[$user->username] = [
            'id' => $user->id,
            'hash' => $this->hashFor($user),
        ];

        $this->saveRawPayload($raw);
    }

    /**
     * Switch to the given account by username.
     */
    public function switchAccount(User|string $user): void
    {
        $username = is_string($user) ? $user : $user->username;
        $accounts = $this->getAll();

        if (! isset($accounts[$username])) {
            abort(403, 'Unauthorized action.');
        }

        $user = $accounts[$username];

        Session::invalidate();
        Auth::guard('web')->login($user);
    }

    /**
     * Remove an account by username.
     */
    public function removeAccount(string $username): void
    {
        $raw = $this->getRawPayload();

        if (isset($raw[$username])) {
            unset($raw[$username]);
            $this->saveRawPayload($raw);
        }
    }

    /**
     * Handle logging out the given user.
     */
    public function logoutAccount(User $user): void
    {
        $this->removeAccount($user->username);
        Auth::guard('web')->logout();

        $remainingAccounts = $this->getAll();

        if ($remainingAccounts !== []) {
            $nextUsername = array_key_last($remainingAccounts);
            $this->switchAccount($nextUsername);

            return;
        }

        Session::invalidate();
        Session::regenerateToken();
        cookie()->queue(cookie()->forget('accounts'));
    }

    /**
     * Generate an HMAC hash verifying the user's remember token.
     */
    public function hashFor(User $user): string
    {
        return hash_hmac('sha256', $user->id.'|'.$user->remember_token, config()->string('app.key'));
    }

    /**
     * Get raw payload from cookie or queued cookie.
     *
     * @return array<string, array{id: int, hash: string}>
     */
    private function getRawPayload(): array
    {
        $queued = cookie()->getQueuedCookies();
        $cookie = collect($queued)->last(fn (Cookie $c): bool => $c->getName() === 'accounts');

        if ($cookie !== null) {
            $rawValue = $cookie->getValue();
        } else {
            $rawValue = Request::cookie('accounts');
        }

        if (! is_string($rawValue) || $rawValue === '') {
            return [];
        }

        $decoded = json_decode($rawValue, true);

        if (! is_array($decoded)) {
            return [];
        }

        /** @var array<string, array{id: int, hash: string}> $result */
        $result = [];

        foreach ($decoded as $key => $value) {
            if (! is_string($key) || ! is_array($value)) {
                continue;
            }

            $id = $value['id'] ?? null;
            $hash = $value['hash'] ?? null;

            if (is_int($id) && is_string($hash)) {
                $result[$key] = [
                    'id' => $id,
                    'hash' => $hash,
                ];
            }
        }

        return $result;
    }

    /**
     * Save raw payload to cookie.
     *
     * @param  array<string, array{id: int, hash: string}>  $data
     */
    private function saveRawPayload(array $data): void
    {
        if ($data === []) {
            cookie()->queue(cookie()->forget('accounts'));

            return;
        }

        $encoded = json_encode($data);

        if ($encoded !== false) {
            cookie()->queue(cookie()->forever('accounts', $encoded));
        }
    }
}
