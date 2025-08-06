<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

final readonly class Accounts
{
    /**
     * Get all accounts from the cookie.
     *
     * @return array<string, bool>
     */
    public static function all(): array
    {
        /**
         * @var array<string, bool>|null $accounts
         */
        $accounts = json_decode(is_string(Request::cookie('accounts')) ? Request::cookie('accounts') : '[]', true);

        return is_array($accounts) ? $accounts : [];
    }

    /**
     * Push a new account to the cookie.
     */
    public static function push(string $username): void
    {
        $accounts = self::all();

        $accounts[$username] = true;

        $accounts = json_encode($accounts);

        if ($accounts !== false) {
            Cookie::queue(Cookie::forever('accounts', $accounts));
        }
    }

    /**
     * Switch the current account.
     */
    public static function switch(string $username): void
    {
        $accounts = self::all();
        if (isset($accounts[$username])) {
            $user = User::where('username', $username)->first();

            if (! $user) {
                abort(403, 'User not found.');
            }

            Session::regenerate();
            Auth::login($user);

            return;
        }

        abort(403, 'Unauthorized action.');
    }

    /**
     * Remove an account from the cookie.
     */
    public static function remove(string $username): void
    {
        $accounts = self::all();
        unset($accounts[$username]);

        $accounts = json_encode($accounts);
        if ($accounts !== false) {
            Cookie::queue(Cookie::forever('accounts', $accounts));
        }
    }
}
