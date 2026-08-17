<?php

declare(strict_types=1);

namespace App\Enums;

enum FeedVisibility: string
{
    case Public = 'public';
    case Private = 'private';

    /**
     * Get the values of the enum as an associative array.
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return [
            self::Public->value => 'Public',
            self::Private->value => 'Private',
        ];
    }
}
