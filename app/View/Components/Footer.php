<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Services\Git;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;
use Illuminate\View\View;

final class Footer extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        $version = Cache::remember('git-latest-tag', 3600, fn () => app(Git::class)->getLatestTag());

        return view('components.footer', [
            'version' => $version,
        ]);
    }
}
