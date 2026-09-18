<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Links\UpdateLinkClicks;
use App\Models\Link;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\IpUtils;

final readonly class LinkController
{
    /**
     * Record a profile link visit, with the web's deduplication: the
     * owner's own taps and repeat visits within a day don't count.
     */
    public function click(Request $request, Link $link, UpdateLinkClicks $updateLinkClicks): JsonResponse
    {
        $cacheKey = IpUtils::anonymize((string) $request->ip()).'-clicked-'.$link->id;

        if ($request->user()->id !== $link->user_id && ! Cache::has($cacheKey)) {
            $updateLinkClicks->handle($link->id, $cacheKey);
        }

        return response()->json(['data' => ['clicked' => true]]);
    }
}
