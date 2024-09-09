<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Autocomplete\Result;
use App\Services\Autocomplete\Types\Hashtags;
use App\Services\Autocomplete\Types\Mentions;
use App\Services\Autocomplete\Types\Type;
use Illuminate\Support\Collection;

final class Autocomplete
{
    /**
     * The registered autocomplete types in the form
     * alias => class name.
     *
     * @var array<string, class-string<Type>>
     */
    private static array $types = [
        'hashtags' => Hashtags::class,
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
     * @return Collection<int, Result>
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
        if ($type instanceof Type) {
            return $type;
        }

        $typeClass = self::typeClassFor($type);

        return new $typeClass;
    }
}
