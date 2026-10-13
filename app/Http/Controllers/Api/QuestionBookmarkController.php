<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Questions\CreateBookmark;
use App\Actions\Questions\DeleteBookmark;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final readonly class QuestionBookmarkController
{
    /** Bookmark a post. */
    public function store(Request $request, Question $question, CreateBookmark $createBookmark): JsonResponse
    {
        Gate::authorize('view', $question);

        /** @var User $user */
        $user = $request->user();

        $createBookmark->handle($question, $user);

        return response()->json(['data' => [
            'bookmarked' => true,
            'bookmarks' => $question->bookmarks()->count(),
        ]]);
    }

    /** Remove the signed-in user's bookmark. */
    public function destroy(Request $request, Question $question, DeleteBookmark $deleteBookmark): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($bookmark = $question->bookmarks()->where('user_id', $user->id)->first()) {
            Gate::authorize('delete', $bookmark);

            $deleteBookmark->handle($bookmark);
        }

        return response()->json(['data' => [
            'bookmarked' => false,
            'bookmarks' => $question->bookmarks()->count(),
        ]]);
    }
}
