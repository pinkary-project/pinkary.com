<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\Question;
use App\Models\User;
use App\Queries\Feeds\FeedQuestion;

final readonly class CreateQuestionToUser
{
    public function __construct(
        private EnsureCanPublish $ensureCanPublish,
    ) {}

    /**
     * Ask a question to another user.
     */
    public function handle(User $from, User $to, string $content, bool $anonymously = false): Question
    {
        $this->ensureCanPublish->handle($from, 1);

        $question = $from->questionsSent()->create([
            'to_id' => $to->id,
            'content' => $content,
            'anonymously' => $anonymously,
        ]);

        return (new FeedQuestion)(Question::query()->whereKey($question->id), $from->id)->firstOrFail();
    }
}
