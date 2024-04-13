<?php

declare(strict_types=1);

namespace App\Contracts;

interface HasToArray
{
    /**
     * Get the values of the enum.
     *
     * @return array<string, string>
     */
    public static function toArray(): array;
}
