<?php

declare(strict_types=1);

namespace App\Queries\Feeds;

use App\Models\Question;
use App\Models\User;
use Illuminate\Pagination\Paginator;

final readonly class FeedQuery
{
    /** Configure feed thread hydration. */
    public function __construct(
        private FeedThread $feedThread,
    ) {}

    /**
     * @return Paginator<int, Question>
     */
    public function paginate(string $tab, int $perPage, ?User $user): Paginator
    {
        $userId = $user?->id;

        if ($tab === 'following' && ! $user instanceof User) {
            return (new FeedQuestion)(Question::query()->whereRaw('1 = 0'), null)->simplePaginate($perPage);
        }

        $paginator = $tab === 'trending'
            ? $this->trending($perPage, $userId)
            : (new FeedQuestion)(
                $tab === 'following'
                    ? new QuestionsFollowingFeed($user)->builder()
                    : (new RecentQuestionsFeed)->builder(),
                $userId,
            )->simplePaginate($perPage);

        return $this->feedThread->attachTo($paginator, $userId);
    }

    /**
     * @return Paginator<int, Question>
     */
    private function trending(int $perPage, ?int $userId): Paginator
    {
        $paginator = (new TrendingQuestionsFeed)->builder()->simplePaginate($perPage);

        /** @var list<string> $ids */
        $ids = $paginator->getCollection()->pluck('id')->all();

        $hydrated = (new FeedQuestion)(Question::query()->select('questions.id')->whereIn('id', $ids), $userId)->get();

        $paginator->setCollection(
            collect($ids)->map(fn (string $id): ?Question => $hydrated->firstWhere('id', $id))->filter()->values()
        );

        return $paginator;
    }
}
