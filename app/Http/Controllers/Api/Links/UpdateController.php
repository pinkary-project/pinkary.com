<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Links;

use App\Actions\Links\UpdateLink;
use App\Http\Requests\Api\UpdateLinkRequest;
use App\Http\Resources\LinkResource;
use App\Models\Link;
use Illuminate\Support\Facades\Gate;

final readonly class UpdateController
{
    public function __invoke(UpdateLinkRequest $request, Link $link, UpdateLink $updateLink): LinkResource
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
}
