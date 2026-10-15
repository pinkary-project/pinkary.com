<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\PaginatedRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Channel;
use App\Models\User;
use App\Queries\Feeds\ChannelQuestionsFeed;
use App\Queries\Feeds\FeedQuestion;
use App\Queries\Feeds\FeedThread;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class ChannelQuestionsController
{
    /** List visible posts in a channel. */
    public function __invoke(
        PaginatedRequest $request,
        Channel $channel,
        FeedThread $feedThread,
    ): AnonymousResourceCollection {
        /** @var User|null $viewer */
        $viewer = $request->user();

        if ($channel->isAdminOnly() && ! $viewer?->isAdmin()) {
            abort(404);
        }

        $viewerId = $viewer?->id;
        $query = (new FeedQuestion)(
            new ChannelQuestionsFeed($channel)->builder()->orderByDesc('questions.id'),
            $viewerId,
        );

        $paginator = $query->simplePaginate($request->perPage());

        return QuestionResource::collection($feedThread->attachTo($paginator, $viewerId));
    }
}
