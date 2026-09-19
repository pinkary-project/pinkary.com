<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\PollVotes;

use App\Actions\Questions\GetThreadedQuestion;
use App\Actions\Questions\UpdatePollVote;
use App\Http\Requests\Api\VotePollRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use Illuminate\Http\JsonResponse;

final readonly class StoreController
{
    public function __invoke(VotePollRequest $request, Question $question, UpdatePollVote $updatePollVote, GetThreadedQuestion $threaded): JsonResponse
    {
        if ($question->poll_expires_at === null) {
            return response()->json(['message' => 'This question is not a poll.'], 422);
        }

        if ($question->isPollExpired()) {
            return response()->json(['message' => 'This poll has expired and voting is no longer allowed.'], 422);
        }

        $option = $question->pollOptions()->whereKey($request->validated('option_id'))->first();

        if (! $option) {
            return response()->json(['message' => 'The selected option is invalid.'], 422);
        }

        $updatePollVote->handle($request->user(), $question, $option);

        $thread = $threaded->handle(Question::query()->findOrFail($question->id), $request->user()?->id);

        return response()->json(['data' => (new QuestionResource($thread['question']))->poll()]);
    }
}
