<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Questions\UpdatePollVote;
use App\Http\Requests\Api\VotePollRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

final readonly class PollVoteController
{
    /** Record the signed-in user's vote on a poll. */
    public function store(
        VotePollRequest $request,
        Question $question,
        UpdatePollVote $updatePollVote,
    ): JsonResponse {
        Gate::authorize('view', $question);

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

        /** @var User $user */
        $user = $request->user();

        $updatePollVote->handle($user, $question, $option);

        $question->loadMissing([
            'pollOptions' => fn (Relation $query) => $query->select('id', 'question_id', 'text', 'votes_count')->orderBy('id'),
            'pollVotes' => fn (Relation $query) => $query->select('id', 'question_id', 'poll_option_id')->where('user_id', $user->id),
        ]);

        return response()->json(['data' => new QuestionResource($question)->poll()]);
    }
}
