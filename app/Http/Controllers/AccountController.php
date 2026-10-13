<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Accounts;
use Illuminate\Http\RedirectResponse;

final readonly class AccountController
{
    /**
     * Switch to another authenticated account.
     */
    public function __invoke(User $user, Accounts $accounts): RedirectResponse
    {
        $accounts->switchAccount($user);

        return back();
    }
}
