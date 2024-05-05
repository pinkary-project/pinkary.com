<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

final readonly class UpdatePasswordController
{
    /**
     * Update the user's password.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $user = type($request->user())->as(User::class);

        try {
            $validated = $request->validateWithBag('updatePassword', [
                'current_password' => ['required', 'current_password'],
                'password' => ['required', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $exception) {
            return redirect()->to(url()->previous().'#update-password')->withErrors($exception->validator, 'updatePassword');
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        session()->flash('flash-message', 'Password updated.');

        return redirect(url()->previous().'#update-password');
    }
}
