<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Queries\Feeds\FeedQuestion;
use App\Queries\Feeds\FeedThread;
use App\Queries\Feeds\QuestionsFollowingFeed;
use App\Queries\Feeds\RecentQuestionsFeed;
use App\Queries\Feeds\TrendingQuestionsFeed;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class FeedController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $tab = $request->validate([
            'tab' => ['sometimes', 'string', 'in:recent,following,trending'],
        ])['tab'] ?? 'recent';

        $perPage = min(max($request->integer('per_page', 20), 1), 50);
        $user = $request->user();
        $userId = $user?->id;

        // Guests read the public tabs like the web; the following feed
        // needs a user, so it comes back empty for them.
        if ($tab === 'following' && $user === null) {
            $paginator = $this->withFeedRelations(
                Question::query()->whereRaw('1 = 0'),
                null,
            )->simplePaginate($perPage);

            return QuestionResource::collection($paginator);
        }

        $paginator = $tab === 'trending'
            ? $this->trendingQuestions($perPage, $userId)
            : $this->withFeedRelations(
                $tab === 'following'
                    ? (new QuestionsFollowingFeed($user))->builder()
                    : (new RecentQuestionsFeed)->builder(),
                $userId,
            )->simplePaginate($perPage);

        $this->attachThreads($paginator->getCollection(), $userId);

        return QuestionResource::collection($paginator);
    }

    /**
     * Load the trending page fully hydrated, preserving the trending order.
     */
    private function trendingQuestions(int $perPage, ?int $userId): \Illuminate\Pagination\Paginator
    {
        $paginator = (new TrendingQuestionsFeed)->builder()->simplePaginate($perPage);

        /** @var list<string> $ids */
        $ids = $paginator->getCollection()->pluck('id')->all();

        $hydrated = $this->withFeedRelations(Question::query()->select('questions.id')->whereIn('id', $ids), $userId)->get();

        $paginator->setCollection(
            collect($ids)->map(fn (string $id): ?Question => $hydrated->firstWhere('id', $id))->filter()->values()
        );

        return $paginator;
    }

    /**
     * Attach each item's visible thread context (root + parent above it),
     * mirroring the web feed's x-thread rows.
     *
     * @param  \Illuminate\Support\Collection<int, Question>  $items
     */
    private function attachThreads(\Illuminate\Support\Collection $items, ?int $userId): void
    {
        $threads = (new FeedThread)->forItems($items, $userId);

        foreach ($items as $item) {
            $thread = $threads[$item->id] ?? null;

            $item->setRelation('threadChain', $thread['posts'] ?? collect());
            $item->setAttribute('threadMore', $thread['more'] ?? false);
            $item->setAttribute('threadMoreId', $thread['more_id'] ?? null);
        }
    }

    /**
     * Apply the columns and relations the mobile feed renders.
     *
     * @param  Builder<Question>  $query
     * @return Builder<Question>
     */
    private function withFeedRelations(Builder $query, ?int $userId): Builder
    {
        return (new FeedQuestion)($query, $userId);
    }
}
