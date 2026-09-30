<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Questions\CreateQuestionToUser;
use App\Actions\Questions\CreateThread;
use App\Actions\Questions\DeleteQuestion;
use App\Http\Requests\Api\StoreQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Models\User;
use App\Queries\Questions\ThreadedQuestionQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

final readonly class QuestionController
{
    /** Publish a new post or thread. */
    public function store(
        StoreQuestionRequest $request,
        CreateThread $createThread,
        CreateQuestionToUser $createQuestionToUser,
    ): JsonResponse {
        $validated = $request->validated();

        /** @var User $user */
        $user = $request->user();

        $recipientUsername = $request->recipientUsername();

        if ($recipientUsername !== null && $recipientUsername !== $user->username) {
            $recipient = User::where('username', $recipientUsername)->firstOrFail();

            $question = $createQuestionToUser->handle(
                $user,
                $recipient,
                $request->content(),
                $request->isAnonymous(),
            );

            return QuestionResource::collection(collect([$question]))->response()->setStatusCode(201);
        }

        $questions = $createThread->handle($user, $validated);

        return QuestionResource::collection($questions)->response()->setStatusCode(201);
    }

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

    /** Delete one of the signed-in user's posts. */
    public function destroy(Request $request, Question $question, DeleteQuestion $deleteQuestion): Response
    {
        Gate::authorize('delete', $question);

        $deleteQuestion->handle($question);

        return response()->noContent();
    }
}
