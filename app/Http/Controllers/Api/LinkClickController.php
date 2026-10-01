<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Links\UpdateLinkClicks;
use App\Models\Link;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class LinkClickController
{
    /** Count a click on a link, for guests and members alike. */
    public function store(Request $request, Link $link, UpdateLinkClicks $updateLinkClicks): JsonResponse
    {
        abort_unless($link->is_visible, Response::HTTP_NOT_FOUND);

        $updateLinkClicks->handle($link, (string) $request->ip(), $request->user()?->id);

        return response()->json(['data' => ['clicked' => true]]);
    }
}
