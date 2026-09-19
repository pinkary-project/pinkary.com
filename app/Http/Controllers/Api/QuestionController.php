<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Questions\CreateThread;
use App\Actions\Questions\GetThreadedQuestion;
use App\Http\Requests\Api\StoreQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class QuestionController
{
    public function store(StoreQuestionRequest $request, CreateThread $createThread): JsonResponse
    {
        $questions = $createThread->handle($request->user(), $request->validated());

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
