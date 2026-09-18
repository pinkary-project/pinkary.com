<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\Scopes\WhereNotModerated;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final readonly class ProfileController
{
    /**
     * Return the authenticated user's profile with the counts and links
     * the profile card renders.
     */
    public function show(Request $request): JsonResource
    {
        /** @var User $user */
        $user = $request->user();

        return new UserResource($this->hydrate($user));
    }

    private function hydrate(User $user): User
    {
        $user->loadMissing(['links' => fn ($query) => $query->where('is_visible', true)]);
        $user->loadCount(['followers', 'following']);

        $user->setAttribute(
            'posts_count',
            $user->questionsReceived()->tap(new WhereNotModerated)->where('answer', '!=', '')->count()
        );

        return $user;
    }
}
