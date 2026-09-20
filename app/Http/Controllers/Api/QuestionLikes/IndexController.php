<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\QuestionLikes;

use App\Http\Requests\Api\PaginatedRequest;
use App\Http\Resources\UserResource;
use App\Models\Question;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

final readonly class IndexController
{
    public function __invoke(PaginatedRequest $request, Question $question): AnonymousResourceCollection
    {
        Gate::authorize('viewLikes', $question);

        $viewerId = $request->user()?->id;

        $likers = $question->likers()
            ->withExists([
                'followers as followed_by_me' => fn ($query) => $query->when(
                    $viewerId,
                    fn ($q) => $q->where('follower_id', $viewerId),
                    fn ($q) => $q->whereRaw('1 = 0')
                ),
            ])
            ->simplePaginate((int) ($request->validated()['per_page'] ?? 20));

        return UserResource::collection($likers);
    }
}
