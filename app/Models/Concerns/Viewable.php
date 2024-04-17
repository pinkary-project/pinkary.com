<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait Viewable
{
    /**
     * Increment the views for the given IDs.
     *
     * @param  array<int, int>  $ids
     */
    public static function incrementViews(array $ids): void
    {
        static::withoutTimestamps(function () use ($ids) {
            static::query()->whereIn('id', $ids)->increment('views');
        });
    }
}
