<?php

declare(strict_types=1);

namespace App\Services\DynamicAutocomplete;

use App\Services\DynamicAutocomplete\Results\Collection;
use App\Services\DynamicAutocomplete\Types\Mentions;
use App\Services\DynamicAutocomplete\Types\Type;

final class DynamicAutocomplete
{
    /**
     * The registered autocomplete types.
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
     * @return class-string<Type>
     */
    public static function typeClassFor(string $type): string
    {
        return self::$types[$type];
    }

    /**
     * Perform an autocompletion search.
     */
    public function search(Type|string $type, string $query): Collection
    {
        $instance = $this->resolveInstance($type);

        return $instance->search(
            $instance->prepareQueryForSearch($query)
        );
    }

    private function resolveInstance(Type|string $type): Type
    {
        return $type instanceof Type
            ? $type
            : self::typeClassFor($type)::make();
    }
}
