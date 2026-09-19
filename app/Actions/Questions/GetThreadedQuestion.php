<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\Question;
use App\Queries\Feeds\FeedQuestion;
use Illuminate\Support\Collection;

final readonly class GetThreadedQuestion
{
    /**
     * Load a question with the feed's relations plus its thread
     * ancestors (root first) for connected display.
     *
     * @return array{question: Question, ancestors: Collection<int, Question>}
     */
    public function handle(Question $question, ?int $userId): array
    {
        $post = (new FeedQuestion)(Question::query()->whereKey($question->id), $userId)->firstOrFail();

        $ids = $post->ancestorIds();

        $ancestors = $ids->isEmpty()
            ? collect()
            : (new FeedQuestion)(Question::query()->whereIn('id', $ids->all()), $userId)
                ->get()->keyBy('id')
                ->pipe(fn (Collection $hydrated) => $ids->map(fn (string $id) => $hydrated->get($id))->filter()->values());

        return ['question' => $post, 'ancestors' => $ancestors];
    }
}
