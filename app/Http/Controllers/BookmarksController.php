<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

final readonly class BookmarksController
{
    /**
     * Display all notifications.
     */
    public function index(): View
    {
        return view('bookmarks.index');
    }
}
