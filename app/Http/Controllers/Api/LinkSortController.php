<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Links\UpdateLinkOrder;
use App\Http\Requests\Api\SortLinksRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final readonly class LinkSortController
{
    /** Reorder the signed-in user's links. */
    public function store(SortLinksRequest $request, UpdateLinkOrder $updateLinkOrder): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $updateLinkOrder->handle($user, $request->order());

        return response()->json(['data' => ['sorted' => true]]);
    }
}
