<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Links;

use App\Actions\Links\UpdateLinkOrder;
use App\Http\Requests\Api\SortLinksRequest;
use Illuminate\Http\JsonResponse;

final readonly class SortController
{
    public function __invoke(SortLinksRequest $request, UpdateLinkOrder $updateLinkOrder): JsonResponse
    {
        $sort = array_map(strval(...), $request->validated('sort'));

        $updateLinkOrder->handle($request->user(), $sort);

        return response()->json(['data' => ['sorted' => true]]);
    }
}
