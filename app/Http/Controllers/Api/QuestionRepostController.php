<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Questions\CreateRepost;
use App\Actions\Questions\DeleteRepost;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final readonly class QuestionRepostController
{
    /** Repost a question. */
    public function store(Request $request, Question $question, CreateRepost $createRepost): JsonResponse
    {
        Gate::authorize('repost', $question);

        /** @var User $user */
        $user = $request->user();

        $createRepost->handle($question, $user);

        return response()->json(['data' => [
            'reposted' => true,
            'reposts' => $question->reposts()->count(),
        ]]);
    }

    /** Remove the signed-in user's repost. */
    public function destroy(Request $request, Question $question, DeleteRepost $deleteRepost): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($repost = $question->reposts()->whereBelongsTo($user)->first()) {
            Gate::authorize('delete', $repost);

            $deleteRepost->handle($repost);
        }

        return response()->json(['data' => [
            'reposted' => false,
            'reposts' => $question->reposts()->count(),
        ]]);
    }
}
