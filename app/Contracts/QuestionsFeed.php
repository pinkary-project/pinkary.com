<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Contracts\Pagination\Paginator;

interface QuestionsFeed
{
    /**
     * Get the questions feed.
     *
     * @return Paginator<\App\Models\Question>
     */
    public function questions(): Paginator;
}
