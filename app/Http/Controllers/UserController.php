<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Users\DeleteUser;
use App\Actions\Users\UpdateUser;
use App\Http\Requests\UserUpdateRequest;
use App\Jobs\IncrementViews;
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
    public function update(
        UserUpdateRequest $request,
        #[CurrentUser] User $user,
        UpdateUser $updateUser,
    ): RedirectResponse {
        $updateUser->handle($user, $request->validated());

        session()->flash('flash-message', 'Profile updated.');

        return to_route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(
        Request $request,
        #[CurrentUser] User $user,
        DeleteUser $deleteUser,
    ): RedirectResponse {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        auth()->logout();

        $deleteUser->handle($user);

        session()->invalidate();
        session()->regenerateToken();

        return redirect()->to('/');
    }
}
