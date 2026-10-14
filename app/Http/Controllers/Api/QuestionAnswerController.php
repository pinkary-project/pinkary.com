<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Questions\UpdateQuestionAnswer;
use App\Http\Requests\Api\UpdateAnswerRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Channel;
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
        $validated = $request->validated();
        $updateChannel = array_key_exists('channel_id', $validated);
        $channelId = isset($validated['channel_id']) ? (int) $validated['channel_id'] : null;

        if ($updateChannel) {
            abort_unless($question->isSharedUpdate() && $question->parent_id === null, 422, 'A channel can only be changed on a root post.');

            if ($channelId !== null) {
                $channel = Channel::query()->findOrFail($channelId);

                abort_if($channel->isAdminOnly() && ! $user->isAdmin(), 403);
            }
        }

        $updated = $updateQuestionAnswer->handle(
            $question,
            $request->answer(),
            $user->id,
            $updateChannel,
            $channelId,
        );

        return new QuestionResource($updated);
    }
}
