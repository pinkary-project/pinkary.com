<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Links\UpdateLinkClicks;
use App\Models\Link;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class LinkController
{
    /**
     * Record a profile link visit, with the web's deduplication: the
     * owner's own taps and repeat visits within a day don't count.
     */
    public function click(Request $request, Link $link, UpdateLinkClicks $updateLinkClicks): JsonResponse
    {
        $updateLinkClicks->handle($link, (string) $request->ip(), $request->user()?->id);

        return response()->json(['data' => ['clicked' => true]]);
    }
}
