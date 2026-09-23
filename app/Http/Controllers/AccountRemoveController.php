<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Accounts;
use Illuminate\Http\RedirectResponse;

final readonly class AccountRemoveController
{
    /**
     * Remove an account from the authenticated accounts list.
     */
    public function __invoke(User $user, Accounts $accounts): RedirectResponse
    {
        $accounts->removeAccount($user->username);

        return back();
    }
}
