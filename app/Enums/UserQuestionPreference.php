<?php

declare(strict_types=1);

namespace App\Enums;

enum UserQuestionPreference: string
{
    case Everyone = 'everyone';
    case Following = 'following';
    case NoOne = 'no_one';

    /**
     * Get the values of the enum as an associative array.
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return [
            self::Everyone->value => 'Everyone',
            self::Following->value => 'Following',
            self::NoOne->value => 'No one',
        ];
    }
}
