<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UserDefaultFeed;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final readonly class HomeController
{
    /**
     * Display the home feed or redirect to the user's preferred feed on initial landing.
     */
    public function __invoke(Request $request): View|RedirectResponse
    {
        /** @var User|null $user */
        $user = auth()->user();

        if ($user instanceof User && ! $request->hasHeader('X-Livewire-Navigate')) {
            return match ($user->default_feed) {
                UserDefaultFeed::Following => redirect()->route('home.following'),
                UserDefaultFeed::Trending => redirect()->route('home.trending'),
                UserDefaultFeed::Recent => view('home/feed'),
            };
        }

        return view('home/feed');
    }
}
