<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

final readonly class HashtagController
{
    /**
     * Display posts for the hashtag.
     */
    public function __invoke(string $hashtag): View
    {
        return view('hashtag.show', [
            'hashtag' => $hashtag,
        ]);
    }
}
