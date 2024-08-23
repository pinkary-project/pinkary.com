<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

final readonly class BookmarksController
{
    /**
     * Display all bookmarks.
     */
    public function index(): View
    {
        return view('bookmarks.index');
    }
}
