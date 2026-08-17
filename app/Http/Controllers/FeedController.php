<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Feed;
use Illuminate\Http\Request;
use Illuminate\View\View;

final readonly class FeedController
{
    /**
     * Display the feeds index / management page.
     */
    public function index(): View
    {
        return view('feeds.index');
    }

    /**
     * Show the form for creating a new feed.
     */
    public function create(): View
    {
        return view('feeds.create');
    }

    /**
     * Display the specified feed.
     */
    public function show(Request $request, Feed $feed): View
    {
        if (! $feed->isPublic() && $feed->user_id !== $request->user()?->id) {
            abort(403);
        }

        return view('feeds.show', [
            'feed' => $feed,
        ]);
    }

    /**
     * Show the form for editing the feed.
     */
    public function edit(Request $request, Feed $feed): View
    {
        if ($feed->user_id !== $request->user()?->id) {
            abort(403);
        }

        return view('feeds.edit', [
            'feed' => $feed,
        ]);
    }
}
