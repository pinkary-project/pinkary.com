<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;

interface QuestionFeedStrategyProvider
{
    /**
     * Get the question builder
     *
     * @return Builder<Question>
     */
    public function getBuilder(): Builder;
}
