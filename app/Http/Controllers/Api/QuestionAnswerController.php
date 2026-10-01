<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Questions\UpdateQuestionAnswer;
use App\Http\Requests\Api\UpdateAnswerRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final readonly class QuestionAnswerController
{
    /** Update the answer on a post. */
    public function update(
        UpdateAnswerRequest $request,
        Question $question,
        UpdateQuestionAnswer $updateQuestionAnswer,
    ): QuestionResource {
        Gate::authorize('update', $question);

        /** @var User $user */
        $user = $request->user();

        $updated = $updateQuestionAnswer->handle(
            $question,
            $request->answer(),
            $user->id,
        );

        return new QuestionResource($updated);
    }
}
