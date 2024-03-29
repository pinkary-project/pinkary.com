<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AccountController
{
    /**
     * Update the user's account information.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'questions_preference' => ['required', 'in:anonymously,public'],
        ]);

        $user = $request->user();
        assert($user instanceof User);

        $user->update([
            'settings->questions_preference' => $validated['questions_preference'],
        ]);

        session()->flash('flash-message', 'Account settings updated.');

        return to_route('profile.edit');
    }
}
