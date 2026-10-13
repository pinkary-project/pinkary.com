<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\PaginatedRequest;
use App\Http\Resources\QuestionResource;
use App\Models\User;
use App\Queries\Feeds\BookmarkedQuestionsFeed;
use App\Queries\Feeds\FeedQuestion;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class BookmarkController
{
    /** List the signed-in user's bookmarked posts. */
    public function index(PaginatedRequest $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        $paginator = (new FeedQuestion)(
            new BookmarkedQuestionsFeed($user->id)->builder(),
            $user->id,
        )->simplePaginate($request->perPage());

        return QuestionResource::collection($paginator);
    }
}
