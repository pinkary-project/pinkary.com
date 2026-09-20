<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Links;

use App\Actions\Links\CreateLink;
use App\Http\Requests\Api\StoreLinkRequest;
use App\Http\Resources\LinkResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class StoreController
{
    public function __invoke(StoreLinkRequest $request, CreateLink $createLink): JsonResponse
    {
        $user = $request->user();
        $linksCount = $user->links()->count();

        if ($linksCount >= 10 && ! $user->is_verified) {
            return response()->json(['message' => 'You can only have 10 links at a time.'], 422);
        }

        if ($linksCount >= 20 && $user->is_verified) {
            return response()->json(['message' => 'You can only have 20 links at a time.'], 422);
        }

        $link = $createLink->handle($user, $request->validated());

        return (new LinkResource($link))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
