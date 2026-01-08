<?php

declare(strict_types=1);

namespace App\Enums;

enum Feeds: string
{
    case Recent = 'recent';
    case Following = 'following';

    /**
     * Get the values of the enum.
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return [
            self::Recent->value => 'Recent',
            self::Following->value => 'Following',
        ];
    }
}
