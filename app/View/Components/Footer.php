<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Exceptions\GitHubException;
use App\Services\GitHub;
use Illuminate\View\Component;
use Illuminate\View\View;

final class Footer extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        try {
            $version = app(GitHub::class)->getSiteVersion();
        } catch (GitHubException) {
            $version = '';
        }

        return view('components.footer', [
            'version' => $version,
        ]);
    }
}
