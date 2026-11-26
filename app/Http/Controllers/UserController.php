<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Users\DeleteUser;
use App\Actions\Users\UpdateUser;
use App\Http\Requests\UserUpdateRequest;
use App\Jobs\IncrementViews;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

final readonly class UserController
{
    /**
     * The date Pinkary stops serving live profiles.
     */
    private const string SUNSET_AT = '2026-12-01 00:00:00';

    /**
     * Display the user's profile form.
     */
    public function edit(#[CurrentUser] User $user): View
    {
        return view('profile.edit', [
            'user' => $user,
            'sunsetDate' => $this->sunsetDate(),
        ]);
    }

    /**
     * Display the user's profile.
     */
    public function show(User $user): View|RedirectResponse|Response
    {
        if ($this->sunsetHasStarted()) {
            if (filled($user->redirect_url)) {
                return redirect()->away($user->redirect_url, 301);
            }

            return response()->view('profile.sunset', [
                'user' => $user,
                'sunsetDate' => $this->sunsetDate(),
            ], Response::HTTP_GONE);
        }

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

    /**
     * Determine if the sunset redirects should be active.
     */
    private function sunsetHasStarted(): bool
    {
        return now()->greaterThanOrEqualTo(
            CarbonImmutable::parse(self::SUNSET_AT),
        );
    }

    /**
     * Get the formatted sunset date.
     */
    private function sunsetDate(): string
    {
        return CarbonImmutable::parse(self::SUNSET_AT)->format('F j, Y');
    }
}
