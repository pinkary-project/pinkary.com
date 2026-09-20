<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Links;

use App\Actions\Links\DeleteLink;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

final readonly class DestroyController
{
    public function __invoke(Request $request, Link $link, DeleteLink $deleteLink): Response
    {
        Gate::authorize('delete', $link);

        $deleteLink->handle($request->user(), $link);

        return response()->noContent();
    }
}
