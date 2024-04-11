<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;

final class Changelog
{
    /**
     * Get the latest release from the changelog.
     *
     * @return array<string, array<string, array<int, string>|string>>
     */
    public function getReleases(): array
    {
        /* @phpstan-ignore-next-line */
        return collect(preg_split(
            '/## Version/',
            File::get(base_path('CHANGELOG.md')),
            -1,
            PREG_SPLIT_NO_EMPTY
        ))
            ->mapWithKeys(function ($block) {
                /** @var string $block */
                $lines = explode("\n", trim($block));
                $version = trim(array_shift($lines));
                /* @phpstan-ignore-next-line */
                $published_at = trim(str_replace('>', '', array_shift($lines)));
                array_shift($lines);
                $changes = collect($lines)
                    ->filter(fn ($line) => !str_starts_with($line, '##'))
                    ->map(fn ($line) => trim(str_replace('- ', '', $line)))
                    ->all();
                return [$version => compact('published_at', 'changes')];
            })->all();
    }
}
