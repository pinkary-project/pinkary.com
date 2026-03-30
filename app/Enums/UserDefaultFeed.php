<?php

declare(strict_types=1);

namespace App\Enums;

enum UserDefaultFeed: string
{
    case Following = 'following';
    case Recent = 'recent';
    case Trending = 'trending';

    /**
     * Get the values of the enum as an associative array.
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return [
            self::Following->value => 'Following',
            self::Recent->value => 'Recent',
            self::Trending->value => 'Trending',
        ];
    }
}
