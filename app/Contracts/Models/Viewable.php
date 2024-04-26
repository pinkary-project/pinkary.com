<?php

declare(strict_types=1);

namespace App\Contracts\Models;

interface Viewable
{
    /**
     * Increment the views for the given IDs.
     *
     * @param  array<int, int>  $ids
     */
    public static function incrementViews(array $ids): void;
}
