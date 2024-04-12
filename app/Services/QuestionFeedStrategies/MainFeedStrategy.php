<?php

declare(strict_types=1);

namespace App\Services\QuestionFeedStrategies;

use App\Contracts\QuestionFeedStrategyProvider;
use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;

final readonly class MainFeedStrategy implements QuestionFeedStrategyProvider
{
    public function getBuilder(): Builder
    {
        return Question::where('answer', '!=', null)
            ->where('is_ignored', false)
            ->where('is_reported', false)
            ->orderByDesc('updated_at');
    }
}
