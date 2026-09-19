<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Links\UpdateLinkClicks;
use App\Models\Link;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class LinkController
{
    public function __invoke(Request $request, Link $link, UpdateLinkClicks $updateLinkClicks): JsonResponse
    {
        $updateLinkClicks->handle($link, (string) $request->ip(), $request->user()?->id);

        return response()->json(['data' => ['clicked' => true]]);
    }
}
