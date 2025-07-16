<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UserUpdateRequest;
use App\Jobs\IncrementViews;
use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final readonly class UserController
{
    /**
     * Display the user's profile form.
     */
    public function edit(#[CurrentUser] User $user): View
    {
        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Display the user's profile.
     */
    public function show(User $user): View
    {
        IncrementViews::dispatchUsingSession($user);

        return view('profile.show', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(UserUpdateRequest $request, #[CurrentUser] User $user): RedirectResponse
    {
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($user->wasChanged('email')) {
            $user->sendEmailVerificationNotification();

            if (! $user->is_uploaded_avatar) {
                UpdateUserAvatar::dispatch($user);
            }
        }
        session()->flash('flash-message', 'Profile updated.');

        return to_route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request, #[CurrentUser] User $user): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        auth()->logout();

        $user->purge();

        session()->invalidate();
        session()->regenerateToken();

        return redirect()->to('/');
    }
}
