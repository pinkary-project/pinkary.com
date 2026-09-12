<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Accounts;
use Illuminate\Http\RedirectResponse;

final readonly class AccountLogoutAllController
{
    /**
     * Log out of all authenticated accounts.
     */
    public function __invoke(Accounts $accounts): RedirectResponse
    {
        $accounts->logoutAllAccounts();

        return to_route('home.feed');
    }
}
