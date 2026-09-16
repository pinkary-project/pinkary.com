<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\QuestionResource;
use App\Models\Question;
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
        $userId = $request->user()?->id;

        if ($tab === 'trending') {
            return QuestionResource::collection($this->trendingQuestions($perPage, $userId));
        }

        $builder = $tab === 'following'
            ? (new QuestionsFollowingFeed($request->user()))->builder()
            : (new RecentQuestionsFeed)->builder();

        return QuestionResource::collection(
            $this->withFeedRelations($builder, $userId)->simplePaginate($perPage)
        );
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
     * Apply the columns and relations the mobile feed renders.
     *
     * @param  Builder<Question>  $query
     * @return Builder<Question>
     */
    private function withFeedRelations(Builder $query, ?int $userId): Builder
    {
        return $query
            ->addSelect('questions.from_id', 'questions.to_id', 'questions.content', 'questions.answer', 'questions.anonymously', 'questions.views', 'questions.created_at', 'questions.answer_created_at', 'questions.answer_updated_at')
            ->with([
                'from:id,name,username,avatar,is_verified,is_company_verified',
                'to:id,name,username,avatar,is_verified,is_company_verified',
            ])
            ->withExists([
                'likes as is_liked' => fn ($query) => $query->when($userId, fn ($query) => $query->where('user_id', $userId)),
                'bookmarks as is_bookmarked' => fn ($query) => $query->when($userId, fn ($query) => $query->where('user_id', $userId)),
            ])
            ->withCount(['likes', 'children']);
    }
}
