<?php

declare(strict_types=1);

namespace App\Services\Autocomplete;

use App\Services\Autocomplete\Results\AutocompleteResult;
use App\Services\Autocomplete\Types\Mentions;
use App\Services\Autocomplete\Types\Type;
use Illuminate\Support\Collection;

final class AutocompleteService
{
    /**
     * The registered autocomplete types in the form
     * alias => class name.
     *
     * @var array<string, class-string<Type>>
     */
    private static array $types = [
        'mentions' => Mentions::class,
    ];

    /**
     * Get the registered autocomplete types.
     *
     * @return array<string, class-string<Type>>
     */
    public static function types(): array
    {
        return self::$types;
    }

    /**
     * Get the type's class name for the provided alias.
     *
     * @return class-string<Type>
     */
    public static function typeClassFor(string $typeAlias): string
    {
        return self::$types[$typeAlias];
    }

    /**
     * Perform an autocompletion search.
     *
     * @return Collection<int, AutocompleteResult>
     */
    public function search(Type|string $type, string $query): Collection
    {
        $instance = $this->resolveInstance($type);

        return $instance->search(
            $instance->prepareQueryForSearch($query)
        );
    }

    /**
     * Resolve the type instance from the provided parameter.
     */
    private function resolveInstance(Type|string $type): Type
    {
        return $type instanceof Type
            ? $type
            : self::typeClassFor($type)::make();
    }
}
