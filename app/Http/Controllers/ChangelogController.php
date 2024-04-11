<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Changelog;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

final readonly class ChangelogController
{
    /**
     * Show the changelog page.
     */
    public function show(Changelog $changelog): View
    {
        return view('changelog', [
            'releases' => Cache::remember(
                'changelog.releases', 120, fn (): array => $changelog->getReleases(),
            ),
        ]);
    }
}
