<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Questions\UpdateQuestionPin;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final readonly class QuestionPinController
{
    /** Pin a post to the signed-in user's profile. */
    public function store(Request $request, Question $question, UpdateQuestionPin $updateQuestionPin): JsonResponse
    {
        Gate::authorize('pin', $question);

        /** @var User $user */
        $user = $request->user();

        $updateQuestionPin->handle($user, $question, true);

        return response()->json(['data' => ['pinned' => true]]);
    }

    /** Unpin a post. */
    public function destroy(Request $request, Question $question, UpdateQuestionPin $updateQuestionPin): JsonResponse
    {
        Gate::authorize('update', $question);

        /** @var User $user */
        $user = $request->user();

        $updateQuestionPin->handle($user, $question, false);

        return response()->json(['data' => ['pinned' => false]]);
    }
}
