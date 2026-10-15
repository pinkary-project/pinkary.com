<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Account;

use App\Actions\Users\DeleteUser;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final readonly class AccountController
{
    /** Revoke every personal access token after confirming the current password. */
    public function __invoke(Request $request, #[CurrentUser] User $user): Response
    {
        $request->validate([
            'current_password' => ['required', 'current_password:sanctum'],
        ]);

        $user->tokens()->delete();

        return response()->noContent();
    }

    /** Permanently delete the account after confirming the current password. */
    public function destroy(
        Request $request,
        #[CurrentUser] User $user,
        DeleteUser $deleteUser,
    ): Response {
        $request->validate([
            'password' => ['required', 'current_password:sanctum'],
        ]);

        $deleteUser->handle($user);

        return response()->noContent();
    }
}
