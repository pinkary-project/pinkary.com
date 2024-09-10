<?php

declare(strict_types=1);

namespace App\Services\Autocomplete\Types;

use App\Services\Autocomplete\Result;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @implements Arrayable<string, string>
 */
abstract readonly class Type implements Arrayable // @pest-arch-ignore-line
{
    /**
     * Creates a new Type instance.
     */
    final public function __construct()
    {
        //
    }

    /**
     * Perform a search using the query to return
     * autocompletion options.
     *
     * @return Collection<int, Result>
     */
    abstract public function search(string $query): Collection;

    /**
     * The delimiter for the autocompletion type.
     */
    abstract public function delimiter(): string;

    /**
     * The regular expression that represents a matching
     * string after the delimiter.
     */
    abstract public function matchExpression(): string;

    /**
     * Get the full regular expression for the type.
     */
    final public function regexExpression(): string
    {
        return "^{$this->delimiter()}{$this->matchExpression()}";
    }

    /**
     * Prepare the provided query to be searched.
     */
    final public function prepareQueryForSearch(string $query): string
    {
        return Str::remove($this->delimiter(), $query);
    }

    /**
     * Convert the type into an array for frontend use.
     *
     * @return array<string, string>
     */
    final public function toArray(): array
    {
        return [
            'expression' => $this->regexExpression(),
        ];
    }
}
