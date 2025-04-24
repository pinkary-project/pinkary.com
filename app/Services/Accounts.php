<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

final class Accounts
{
    /**
     * Get all accounts from the cookie.
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        $accounts = json_decode((string) request()->cookie('accounts'), true);
        $accounts = is_array($accounts) ? $accounts : [];

        return $accounts;
    }

    /**
     * Push a new account to the cookie.
     */
    public static function push(string $username): void
    {
        $accounts = self::all();

        $accounts = array_unique([
            $username => session()->getId(),
            ...$accounts,
        ]);

        cookie()->queue(cookie()->forever('accounts', json_encode($accounts)));
    }

    /**
     * Switch the current account.
     */
    public static function switch(string $username): void
    {
        $accounts = self::all();
        if (isset($accounts[$username])) {
            session()->setId($accounts[$username]);
            auth()->login(User::where('username', $username)->first());

            return;
        }

        abort(403, 'Unauthorized action.');
    }
}
