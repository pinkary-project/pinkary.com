<?php

declare(strict_types=1);

namespace App\Services\DynamicAutocomplete\Types;

use App\Services\DynamicAutocomplete\Results\Collection;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

/**
 * @implements Arrayable<string, string>
 */
abstract class Type implements Arrayable
{
    final public function __construct()
    {
        //
    }

    abstract public function search(string $query): Collection;

    abstract public function delimiter(): string;

    abstract public function matchExpression(): string;

    final public static function make(): static
    {
        return new static();
    }

    final public function regexExpression(): string
    {
        return "^{$this->delimiter()}{$this->matchExpression()}";
    }

    final public function prepareQueryForSearch(string $query): string
    {
        return Str::remove($this->delimiter(), $query);
    }

    final public function toArray(): array
    {
        return [
            'expression' => $this->regexExpression(),
        ];
    }
}
