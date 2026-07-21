<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Enums\UserDefaultFeed;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final readonly class HomeFeedResponse
{
    /**
     * Create the home feed response, redirecting to the user's preferred feed on fresh page loads.
     */
    public function toResponse(Request $request): View|RedirectResponse
    {
        /** @var User|null $user */
        $user = auth()->user();

        if ($user instanceof User
            && $user->default_feed !== UserDefaultFeed::Recent
            && ! $request->hasHeader('X-Livewire-Navigate')
            && ! $request->cookies->has('_home_redirected')
        ) {
            return redirect()->route($user->default_feed->toRouteName())
                ->withCookie(cookie('_home_redirected', true));
        }

        if ($user instanceof User && ! $request->cookies->has('_home_redirected')) {
            cookie()->queue(cookie('_home_redirected', true));
        }

        return view('home/feed');
    }
}
