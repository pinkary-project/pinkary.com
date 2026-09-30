<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Questions\CreateLike;
use App\Actions\Questions\DeleteLike;
use App\Http\Requests\Api\PaginatedRequest;
use App\Http\Resources\UserResource;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

final readonly class QuestionLikeController
{
    /**
     * The liker list stays owner-only (`QuestionPolicy::viewLikes`) and
     * authenticated, matching the web's likes modal. It exposes no
     * viewer-specific data, so the only thing keeping it closed is the
     * policy, not the data itself.
     */
    public function index(PaginatedRequest $request, Question $question): AnonymousResourceCollection
    {
        Gate::authorize('viewLikes', $question);

        /** @var User $viewer */
        $viewer = $request->user();
        $viewerId = $viewer->id;

        $likers = $question->likers()
            // Same two flags the web computes (Livewire\Likes\Index:44-51).
            ->withExists([
                'followers as followed_by_me' => fn (Builder $query) => $query->when(
                    $viewerId,
                    fn (Builder $q) => $q->where('follower_id', $viewerId),
                    fn (Builder $q) => $q->whereRaw('1 = 0')
                ),
                'following as follows_me' => fn (Builder $query) => $query->when(
                    $viewerId,
                    fn (Builder $q) => $q->where('user_id', $viewerId),
                    fn (Builder $q) => $q->whereRaw('1 = 0')
                ),
            ])
            // Newest like first, id as tie-break (Likes\Index:42-43).
            ->latest('likes.created_at')
            ->latest('likes.id')
            ->simplePaginate($request->perPage());

        return UserResource::collection($likers);
    }

    /** Like a post. */
    public function store(Request $request, Question $question, CreateLike $createLike): JsonResponse
    {
        Gate::authorize('view', $question);

        /** @var User $user */
        $user = $request->user();

        $createLike->handle($question, $user);

        return response()->json(['data' => [
            'liked' => true,
            'likes' => $question->likes()->count(),
        ]]);
    }

    /** Remove the signed-in user's like. */
    public function destroy(Request $request, Question $question, DeleteLike $deleteLike): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($like = $question->likes()->where('user_id', $user->id)->first()) {
            Gate::authorize('delete', $like);

            $deleteLike->handle($like);
        }

        return response()->json(['data' => [
            'liked' => false,
            'likes' => $question->likes()->count(),
        ]]);
    }
}
