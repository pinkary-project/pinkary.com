<?php

declare(strict_types=1);

namespace App\Services\QuestionFeedStrategies;

use App\Contracts\QuestionFeedStrategyProvider;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final readonly class ForYouFeedStrategy implements QuestionFeedStrategyProvider
{
    /**
     * Create a new instance of the ForYouFeedStrategy strategy.
     */
    public function __construct(
        private User $user,
    ) {
    }

    public function getBuilder(): Builder
    {
        return Question::query()
            ->whereHas('to', function (Builder $qToUser): void {
                $qToUser
                    ->whereHas('questionsSent.likes', function (Builder $qLike): void {
                        $qLike->where('user_id', $this->user->id);
                    })
                    ->orWhereHas('questionsReceived.likes', function (Builder $qLike): void {
                        $qLike->where('user_id', $this->user->id);
                    });
            })
            ->whereNotNull('answer')
            ->where('is_reported', false);
    }
}
