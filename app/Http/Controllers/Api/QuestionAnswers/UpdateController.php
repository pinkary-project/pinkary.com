<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\QuestionAnswers;

use App\Actions\Questions\UpdateQuestionAnswer;
use App\Http\Requests\Api\UpdateAnswerRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use Illuminate\Support\Facades\Gate;

final readonly class UpdateController
{
    public function __invoke(
        UpdateAnswerRequest $request,
        Question $question,
        UpdateQuestionAnswer $updateQuestionAnswer,
    ): QuestionResource {
        Gate::authorize('update', $question);

        $updated = $updateQuestionAnswer->handle(
            $question,
            (string) $request->validated('answer'),
            $request->user()->id,
        );

        return new QuestionResource($updated);
    }
}
