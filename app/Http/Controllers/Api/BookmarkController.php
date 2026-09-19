<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\PaginatedRequest;
use App\Http\Resources\QuestionResource;
use App\Queries\Feeds\BookmarkedQuestionsFeed;
use App\Queries\Feeds\FeedQuestion;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class BookmarkController
{
    public function index(PaginatedRequest $request): AnonymousResourceCollection
    {
        $userId = $request->user()->id;

        $paginator = (new FeedQuestion)(
            (new BookmarkedQuestionsFeed($userId))->builder(),
            $userId,
        )->simplePaginate($request->validated()['per_page'] ?? 20);

        return QuestionResource::collection($paginator);
    }
}
