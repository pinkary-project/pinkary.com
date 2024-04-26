<?php

declare(strict_types=1);

namespace App\Enums;

enum UserMailPreference: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Never = 'never';

    /**
     * Get the values of the enum.
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return [
            self::Daily->value => 'Daily',
            self::Weekly->value => 'Weekly',
            self::Never->value => 'Never',
        ];
    }
}
