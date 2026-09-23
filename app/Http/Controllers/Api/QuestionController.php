<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Questions\CreateQuestionToUser;
use App\Actions\Questions\CreateThread;
use App\Actions\Questions\GetThreadedQuestion;
use App\Http\Requests\Api\StoreQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class QuestionController
{
    public function store(
        StoreQuestionRequest $request,
        CreateThread $createThread,
        CreateQuestionToUser $createQuestionToUser,
    ): JsonResponse {
        $validated = $request->validated();
        $user = $request->user();

        if (isset($validated['to_username']) && is_string($validated['to_username']) && $validated['to_username'] !== $user->username) {
            $recipient = User::where('username', $validated['to_username'])->firstOrFail();

            $question = $createQuestionToUser->handle(
                $user,
                $recipient,
                $validated['content'],
                (bool) ($validated['anonymously'] ?? false),
            );

            return QuestionResource::collection(collect([$question]))->response()->setStatusCode(201);
        }

        $questions = $createThread->handle($user, $validated);

        return QuestionResource::collection($questions)->response()->setStatusCode(201);
    }

    public function show(Request $request, Question $question, GetThreadedQuestion $threaded): QuestionResource
    {
        $thread = $threaded->handle($question, $request->user()?->id);

        return (new QuestionResource($thread['question']))->additional([
            'thread' => QuestionResource::collection($thread['ancestors']),
        ]);
    }
}
