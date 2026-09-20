<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Questions;

use App\Actions\Questions\DeleteQuestion;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

final readonly class DestroyController
{
    public function __invoke(Request $request, Question $question, DeleteQuestion $deleteQuestion): Response
    {
        Gate::authorize('delete', $question);

        $deleteQuestion->handle($question);

        return response()->noContent();
    }
}
