<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\QuestionComments;

use App\Actions\Questions\EnsureCanPublish;
use App\Actions\Questions\GetThreadedQuestion;
use App\Http\Requests\Api\StoreCommentRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final readonly class StoreController
{
    public function __invoke(StoreCommentRequest $request, Question $question, GetThreadedQuestion $threaded, EnsureCanPublish $ensureCanPublish): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $ensureCanPublish->handle($user);

        $comment = $user->questionsSent()->create([
            'to_id' => $user->id,
            'content' => $request->validated('content'),
            'parent_id' => $question->id,
            'root_id' => $question->root_id ?? $question->id,
        ]);

        $thread = $threaded->handle(Question::query()->findOrFail($comment->id), $user->id);

        return (new QuestionResource($thread['question']))->response()->setStatusCode(201);
    }
}
