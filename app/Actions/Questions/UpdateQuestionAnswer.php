<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\Question;
use App\Queries\Feeds\FeedQuestion;

final readonly class UpdateQuestionAnswer
{
    /** Create a new action instance. */
    public function __construct(
        private UpdateQuestion $updateQuestion,
    ) {}

    /** Update or provide the answer to a question within the 24h window. */
    public function handle(Question $question, string $answer, int $userId, bool $updateChannel = false, ?int $channelId = null): Question
    {
        if ($question->answer_created_at !== null && $question->answer_created_at->diffInHours(now()) > 24) {
            abort(422, 'Answer cannot be edited after 24 hours.');
        }

        $attributes = ['answer' => $answer];

        if ($question->answer === null) {
            $attributes['answer_created_at'] = now();
        } else {
            $attributes['answer_updated_at'] = now();
        }

        if ($updateChannel) {
            $attributes['channel_id'] = $channelId;
        }

        $this->updateQuestion->handle(
            $question,
            $attributes,
            $updateChannel,
            $question->channel_id,
            clearLikes: false,
        );

        if ($question->wasChanged('answer') && array_key_exists('answer_updated_at', $attributes)) {
            $question->likes()->delete();
        }

        return (new FeedQuestion)(Question::query()->whereKey($question->id), $userId)->firstOrFail();
    }
}
