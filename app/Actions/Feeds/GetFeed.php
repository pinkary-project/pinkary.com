<?php

declare(strict_types=1);

namespace App\Actions\Feeds;

use App\Models\Question;
use App\Models\User;
use App\Queries\Feeds\FeedQuestion;
use App\Queries\Feeds\FeedThread;
use App\Queries\Feeds\QuestionsFollowingFeed;
use App\Queries\Feeds\RecentQuestionsFeed;
use App\Queries\Feeds\TrendingQuestionsFeed;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

final readonly class GetFeed
{
    /**
     * Paginate the requested tab with feed relations and thread context.
     * Guests read the public tabs like the web; the following feed
     * needs a user, so it comes back empty for them.
     */
    public function handle(string $tab, int $perPage, ?User $user): Paginator
    {
        $userId = $user?->id;

        if ($tab === 'following' && $user === null) {
            return (new FeedQuestion)(Question::query()->whereRaw('1 = 0'), null)->simplePaginate($perPage);
        }

        $paginator = $tab === 'trending'
            ? $this->trending($perPage, $userId)
            : (new FeedQuestion)(
                $tab === 'following'
                    ? (new QuestionsFollowingFeed($user))->builder()
                    : (new RecentQuestionsFeed)->builder(),
                $userId,
            )->simplePaginate($perPage);

        /** @var Collection<int, Question> $items */
        $items = $paginator->getCollection();
        $threads = (new FeedThread)->forItems($items, $userId);

        foreach ($items as $item) {
            $thread = $threads[$item->id] ?? null;

            $item->setRelation('threadChain', $thread['posts'] ?? collect());
            $item->setAttribute('threadMore', $thread['more'] ?? false);
            $item->setAttribute('threadMoreId', $thread['more_id'] ?? null);
        }

        return $paginator;
    }

    /**
     * Load the trending page fully hydrated, preserving the trending order.
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
