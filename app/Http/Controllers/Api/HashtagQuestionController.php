<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\PaginatedRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Hashtag;
use App\Queries\Feeds\FeedQuestion;
use App\Queries\Feeds\FeedThread;
use App\Queries\Feeds\RecentQuestionsFeed;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class HashtagQuestionController
{
    /** List public posts that contain a hashtag. */
    public function index(
        PaginatedRequest $request,
        Hashtag $hashtag,
        FeedThread $feedThread,
    ): AnonymousResourceCollection {
        $viewerId = $request->user()?->id;

        $paginator = (new FeedQuestion)(
            new RecentQuestionsFeed($hashtag->name)->builder(),
            $viewerId,
        )->simplePaginate($request->perPage());

        return QuestionResource::collection($feedThread->attachTo($paginator, $viewerId));
    }
}
