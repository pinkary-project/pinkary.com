<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\QuestionPins;

use App\Actions\Questions\UpdateQuestionPin;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final readonly class DestroyController
{
    public function __invoke(Request $request, Question $question, UpdateQuestionPin $updateQuestionPin): JsonResponse
    {
        Gate::authorize('update', $question);

        $updateQuestionPin->handle($request->user(), $question, false);

        return response()->json(['data' => ['pinned' => false]]);
    }
}
