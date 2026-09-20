<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Notifications;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

final readonly class DestroyController
{
    public function __invoke(Request $request, string $id): Response
    {
        $request->user()->notifications()->where('id', $id)->firstOrFail()->delete();

        return response()->noContent();
    }
}
