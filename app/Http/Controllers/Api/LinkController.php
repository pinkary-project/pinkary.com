<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Links\CreateLink;
use App\Actions\Links\DeleteLink;
use App\Actions\Links\UpdateLink;
use App\Http\Requests\Api\StoreLinkRequest;
use App\Http\Requests\Api\UpdateLinkRequest;
use App\Http\Resources\LinkResource;
use App\Models\Link;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

final readonly class LinkController
{
    /** Add a link to the signed-in user's profile. */
    public function store(StoreLinkRequest $request, CreateLink $createLink): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $link = $createLink->handle($user, $request->attributes());

        return new LinkResource($link)->response()->setStatusCode(201);
    }

    /** Update one of the signed-in user's links. */
    public function update(UpdateLinkRequest $request, Link $link, UpdateLink $updateLink): LinkResource
    {
        Gate::authorize('update', $link);

        $validated = $request->validated();

        $attributes = [
            'description' => $validated['description'] ?? $link->description,
            'url' => $validated['url'] ?? $link->url,
        ];

        if (array_key_exists('is_visible', $validated)) {
            $attributes['is_visible'] = $validated['is_visible'];
        }

        $updateLink->handle($link, $attributes);

        return new LinkResource($link->fresh());
    }

    /** Delete one of the signed-in user's links. */
    public function destroy(Request $request, Link $link, DeleteLink $deleteLink): Response
    {
        Gate::authorize('delete', $link);

        /** @var User $user */
        $user = $request->user();

        $deleteLink->handle($user, $link);

        return response()->noContent();
    }
}
