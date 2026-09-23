<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\QuestionLikes;

use App\Actions\Questions\CreateLike;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class StoreController
{
    public function __invoke(Request $request, Question $question, CreateLike $createLike): JsonResponse
    {
        $createLike->handle($question, $request->user());

        return response()->json(['data' => [
            'liked' => true,
            'likes' => $question->likes()->count(),
        ]]);
    }
}
