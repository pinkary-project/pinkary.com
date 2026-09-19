<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\QuestionBookmarks;

use App\Actions\Questions\CreateBookmark;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class StoreController
{
    public function __invoke(Request $request, Question $question, CreateBookmark $createBookmark): JsonResponse
    {
        $createBookmark->handle($question, $request->user());

        return response()->json(['data' => [
            'bookmarked' => true,
            'bookmarks' => $question->bookmarks()->count(),
        ]]);
    }
}
