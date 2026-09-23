<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Channels\GetChannels;
use App\Http\Requests\Api\ChannelRequest;
use App\Models\Channel;
use Illuminate\Http\JsonResponse;

final readonly class ChannelController
{
    public function index(ChannelRequest $request, GetChannels $getChannels): JsonResponse
    {
        $channels = $getChannels->handle(
            mb_trim((string) $request->validated('q', '')),
            $request->user()->isAdmin(),
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
