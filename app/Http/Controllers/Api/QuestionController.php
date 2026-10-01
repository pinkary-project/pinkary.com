<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Queries\Questions\ThreadedQuestionQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final readonly class QuestionController
{
    /**
     * Show a single post. Mirrors the web: the `view` policy keeps
     * unanswered, ignored and reported posts off the public surface,
     * and works for guests because it accepts a nullable user.
     */
    public function show(Request $request, Question $question, ThreadedQuestionQuery $threaded): QuestionResource
    {
        Gate::authorize('view', $question);

        $thread = $threaded->get($question, $request->user()?->id);

        return new QuestionResource($thread['question'])->additional([
            'thread' => QuestionResource::collection($thread['ancestors']),
        ]);
    }
}
