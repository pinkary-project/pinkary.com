<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ChannelRequest;
use App\Http\Requests\Api\PaginatedRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Channel;
use App\Models\User;
use App\Queries\Channels\ChannelsQuery;
use App\Queries\Feeds\ChannelQuestionsFeed;
use App\Queries\Feeds\FeedQuestion;
use App\Queries\Feeds\FeedThread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class ChannelController
{
    /** List channels, including admin-only ones for admins. */
    public function index(ChannelRequest $request, ChannelsQuery $channelsQuery): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $channels = $channelsQuery->get(
            $request->search(),
            $user->isAdmin(),
        );

        return response()->json([
            'data' => $channels->map(fn (Channel $channel): array => [
                'id' => $channel->id,
                'name' => $channel->name,
                'slug' => $channel->slug,
            ])->all(),
        ]);
    }

    /** List visible posts in a channel. */
    public function questions(
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
