<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ChannelRequest;
use App\Models\Channel;
use App\Models\User;
use App\Queries\Channels\ChannelsQuery;
use Illuminate\Http\JsonResponse;

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
}
