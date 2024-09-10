<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class ProfileController
{
    /**
     * Allow the user to fetch their own profile.
     */
    public function show(Request $request): Response
    {
        return (new UserResource(
            resource: type($request->user())->as(User::class),
        ))->toResponse(
            request: $request,
        );
    }
}
