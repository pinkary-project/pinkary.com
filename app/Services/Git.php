<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Process;

final readonly class Git
{
    /**
     * Get the current version of the site.
     */
    public function getLatestTag(): string
    {
        return trim(
            Process::path(base_path())->run(['git', 'describe', '--tags', '--abbrev=0'])->output(),
        );
    }
}
