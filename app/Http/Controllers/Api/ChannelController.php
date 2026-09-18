<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Channel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class ChannelController
{
    /**
     * List popular channels, or search them by name — mirroring the
     * web composer's channel picker (admin-only hidden from non-admins).
     */
    public function index(Request $request): JsonResponse
    {
        $query = mb_trim((string) $request->string('q', ''));

        $channels = $query === ''
            ? Channel::query()
                ->orderByDesc('questions_count')
                ->orderBy('name')
                ->limit(8)
                ->get()
            : Channel::query()
                ->where('name', 'like', "%{$query}%")
                ->orderByDesc('questions_count')
                ->orderBy('name')
                ->limit(8)
                ->get();

        if (! $request->user()->isAdmin()) {
            $channels = $channels->reject(fn (Channel $channel): bool => $channel->isAdminOnly())->values();
        }

        return response()->json([
            'data' => $channels->map(fn (Channel $channel): array => [
                'id' => $channel->id,
                'name' => $channel->name,
                'slug' => $channel->slug,
            ])->all(),
        ]);
    }
}
