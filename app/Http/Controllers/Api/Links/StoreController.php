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
        $link = $createLink->handle($request->user(), $request->validated());

        return (new LinkResource($link))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
