<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\QuestionLikes;

use App\Actions\Questions\DeleteLike;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final readonly class DestroyController
{
    public function __invoke(Request $request, Question $question, DeleteLike $deleteLike): JsonResponse
    {
        if ($like = $question->likes()->where('user_id', $request->user()->id)->first()) {
            Gate::authorize('delete', $like);

            $deleteLike->handle($like);
        }

        return response()->json(['data' => [
            'liked' => false,
            'likes' => $question->likes()->count(),
        ]]);
    }
}
