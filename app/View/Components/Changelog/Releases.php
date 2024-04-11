<?php

declare(strict_types=1);

namespace App\View\Components\Changelog;

use App\Services\Changelog as ChangelogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;
use Illuminate\View\View;

final class Releases extends Component
{
    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('components.changelog.releases', [
            'releases' => Cache::remember('changelog.releases', 120, fn () =>
                app(ChangelogService::class)->getReleases()
            ),
        ]);
    }
}
