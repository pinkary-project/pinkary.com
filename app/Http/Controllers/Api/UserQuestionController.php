<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\PaginatedRequest;
use App\Http\Resources\QuestionResource;
use App\Models\User;
use App\Queries\Feeds\FeedQuestion;
use App\Queries\Feeds\FeedThread;
use App\Queries\Feeds\UserQuestionsFeed;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class UserQuestionController
{
    /** List a user's posts, newest thread activity first. */
    public function index(PaginatedRequest $request, User $user, FeedThread $feedThread): AnonymousResourceCollection
    {
        $viewerId = $request->user()?->id;
        $perPage = $request->perPage();

        $paginator = (new FeedQuestion)(
            new UserQuestionsFeed($user, $viewerId)->builder(),
            $viewerId,
        )->simplePaginate($perPage);

        return QuestionResource::collection($feedThread->attachTo($paginator, $viewerId));
    }
}
