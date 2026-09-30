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
        // Stricter than the web on purpose. Livewire's Links\Index::click()
        // does a bare findOrFail with no visibility check, which was
        // survivable while only signed-in visitors could reach it. Now
        // that this route is public, counting taps on a hidden link would
        // inflate a counter nobody can see -- there is no such traffic.
        abort_unless($link->is_visible, Response::HTTP_NOT_FOUND);

        $updateLinkClicks->handle($link, (string) $request->ip(), $request->user()?->id);

        return response()->json(['data' => ['clicked' => true]]);
    }
}
