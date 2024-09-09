<?php

declare(strict_types=1);

namespace App\Services\Autocomplete\Types;

use App\Models\Hashtag;
use App\Services\Autocomplete\Result;
use Illuminate\Support\Collection;

final readonly class Hashtags extends Type
{
    /**
     * The delimiter for the autocompletion type.
     */
    public function delimiter(): string
    {
        return '#';
    }

    /**
     * The regular expression that represents a matching
     * string after the delimiter.
     */
    public function matchExpression(): string
    {
        return '[a-zA-Z0-9]+';
    }

    /**
     * Perform a search using the query to return
     * autocompletion options.
     *
     * @return Collection<int, Result>
     */
    public function search(?string $query): Collection
    {
        return Hashtag::query()
            ->withCount('questions')
            ->where('name', 'like', "$query%")
            ->orderByDesc('questions_count')
            ->limit(50)
            ->get()
            ->unique(fn (Hashtag $hashtag): string => mb_strtolower($hashtag->name))
            ->take(8)
            ->map(fn (Hashtag $hashtag): Result => new Result(
                id: $hashtag->id,
                replacement: "#{$hashtag->name}",
                view: 'components.autocomplete.hashtag-item',
            ));
    }
}
