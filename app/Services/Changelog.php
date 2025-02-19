<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;

final readonly class Changelog
{
    /**
     * Get the latest release from the changelog.
     *
     * @return array<string, array<string, array<int, string>|string>>
     */
    public function getReleases(): array
    {
        $blocks = preg_split(
            '/## Version/',
            File::get(base_path('CHANGELOG.md')),
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        assert(is_array($blocks));

        return collect($blocks)
            ->mapWithKeys(function (string $block): array {
                /** @var string $block */
                $lines = explode("\n", trim($block));
                $version = trim(array_shift($lines));

                $publishedAt = trim(str_replace('>', '', (string) array_shift($lines)));
                array_shift($lines);
                $changes = collect($lines)
                    ->filter(fn (string $line): bool => ! str_starts_with($line, '##'))
                    ->map(fn (string $line): string => trim(str_replace('- ', '', $line)))
                    ->all();

                return [$version => ['publishedAt' => $publishedAt, 'changes' => $changes]];
            })->all();
    }
}
