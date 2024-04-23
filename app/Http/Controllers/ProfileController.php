<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Jobs\IncrementViews;
use App\Jobs\UpdateUserAvatar;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final readonly class ProfileController
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
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
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = type($request->user())->as(User::class);

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
            $user->sendEmailVerificationNotification();
        }

        $user->save();

        if (! $user->is_uploaded_avatar) {
            UpdateUserAvatar::dispatch($user);
        }

        session()->flash('flash-message', 'Profile updated.');

        return to_route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = type($request->user())->as(User::class);

        auth()->logout();

        $user->purge();

        session()->invalidate();
        session()->regenerateToken();

        return redirect()->to('/');
    }
}
