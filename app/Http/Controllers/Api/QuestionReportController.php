<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Questions\UpdateQuestionStatus;
use App\Models\Question;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

final readonly class QuestionReportController
{
    /** Report a received unanswered question. */
    public function __invoke(Question $question, UpdateQuestionStatus $updateQuestionStatus): Response
    {
        Gate::authorize('update', $question);

        abort_if($question->answer !== null, 422, 'Only unanswered questions can be reported.');

        $updateQuestionStatus->handle($question, reported: true);

        return response()->noContent();
    }
}
