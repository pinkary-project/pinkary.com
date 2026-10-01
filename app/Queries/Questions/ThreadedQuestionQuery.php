<?php

declare(strict_types=1);

namespace App\Queries\Questions;

use App\Models\Question;
use App\Models\Scopes\WhereNotModerated;
use App\Queries\Feeds\FeedQuestion;
use Illuminate\Support\Collection;

final readonly class ThreadedQuestionQuery
{
    /**
     * Load a question with the feed's relations plus its thread
     * ancestors (root first) for connected display.
     *
     * @return array{question: Question, ancestors: Collection<int, Question>}
     */
    public function get(Question $question, ?int $userId): array
    {
        $post = (new FeedQuestion)(Question::query()->whereKey($question->id), $userId)->firstOrFail();

        $ids = $post->ancestorIds();

        // Ancestors come back through the public post endpoint, so they
        // need the same moderated filter the web's `parent` relation has.
        /** @var Collection<int, Question> $ancestors */
        $ancestors = $ids->isEmpty()
            ? collect()
            : (new FeedQuestion)(
                Question::query()->whereIn('id', $ids->all())->tap(new WhereNotModerated),
                $userId,
            )
                ->get()->keyBy('id')
                ->pipe(fn (Collection $hydrated) => $ids->map(fn (string $id): ?Question => $hydrated->get($id))->filter()->values());

        return ['question' => $post, 'ancestors' => $ancestors];
    }
}
