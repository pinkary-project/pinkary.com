<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\Requests\ProfileUpdateRequest;
use App\Jobs\DownloadUserAvatar;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        return view('profile.show', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        assert($user instanceof User);

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        dispatch(new DownloadUserAvatar($user));

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

        $user = $request->user();
        assert($user instanceof User);

        if ($user->avatar) {
            Storage::disk('public')->delete(
                str_replace('storage/', '', $user->avatar)
            );
        }

        assert($user instanceof User);

        auth()->logout();

        $user->delete();

        session()->invalidate();
        session()->regenerateToken();

        return redirect()->to('/');
    }
}
