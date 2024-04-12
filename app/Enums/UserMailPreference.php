<?php

declare(strict_types=1);

namespace App\Enums;

enum UserMailPreference: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
}
