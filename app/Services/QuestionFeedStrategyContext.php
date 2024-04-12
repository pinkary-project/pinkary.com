<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\QuestionFeedStrategyProvider;
use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;

final readonly class QuestionFeedStrategyContext
{
    /**
     * Create a new instance of the QuestionFeedStrategyContext strategy context.
     */
    public function __construct(
        private QuestionFeedStrategyProvider $feedStrategy,
    ) {
    }

    /**
     * @return Builder<Question>
     */
    public function getBuilder(): Builder
    {
        return $this->feedStrategy->getBuilder();
    }
}
