<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\QuestionBookmarks;

use App\Actions\Questions\DeleteBookmark;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final readonly class DestroyController
{
    public function __invoke(Request $request, Question $question, DeleteBookmark $deleteBookmark): JsonResponse
    {
        if ($bookmark = $question->bookmarks()->where('user_id', $request->user()->id)->first()) {
            Gate::authorize('delete', $bookmark);

            $deleteBookmark->handle($bookmark);
        }

        return response()->json(['data' => [
            'bookmarked' => false,
            'bookmarks' => $question->bookmarks()->count(),
        ]]);
    }
}
