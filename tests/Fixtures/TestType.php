<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use App\Services\Autocomplete\Types\Type;
use Illuminate\Support\Collection;

final readonly class TestType extends Type
{
    public function search(string $query): Collection
    {
        return new Collection([$query]); // @phpstan-ignore-line
    }

    public function delimiter(): string
    {
        return '/';
    }

    public function matchExpression(): string
    {
        return '[a-z]+';
    }
}
