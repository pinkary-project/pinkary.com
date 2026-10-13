<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;

/**
 * Restrict the query to questions that have not been ignored or reported.
 *
 * @template TModel of Question
 */
final class WhereNotModerated
{
    /**
     * @param  Builder<Question>  $query
     */
    public function __invoke(Builder $query): void
    {
        $query->where('is_ignored', false)->where('is_reported', false);
    }
}
