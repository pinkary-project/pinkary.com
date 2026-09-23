<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\QuestionIgnores;

use App\Actions\Questions\UpdateQuestionStatus;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final readonly class StoreController
{
    public function __invoke(Request $request, Question $question, UpdateQuestionStatus $updateQuestionStatus): JsonResponse
    {
        Gate::authorize('ignore', $question);

        $updateQuestionStatus->handle($question, ignored: true);

        return response()->json(['data' => ['ignored' => true]]);
    }
}
