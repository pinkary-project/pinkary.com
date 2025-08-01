<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

final readonly class UpdatePasswordController
{
    /**
     * Update the user's password.
     */
    public function __invoke(Request $request, #[CurrentUser] User $user): RedirectResponse
    {
        /** @var array<string, string> $validated */
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        session()->flash('flash-message', 'Password updated.');

        return back();
    }
}
