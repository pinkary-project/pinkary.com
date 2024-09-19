<?php

declare(strict_types=1);

namespace App\Enums;

enum HomePageTabs: string
{
    case Feed = 'feed';
    case Following = 'following';

    /**
     * Get the values of the enum.
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return [
            self::Feed->value => 'Feed',
            self::Following->value => 'Following',
        ];
    }
}
