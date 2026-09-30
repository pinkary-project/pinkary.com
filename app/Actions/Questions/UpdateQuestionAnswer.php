<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\Question;
use App\Queries\Feeds\FeedQuestion;

final readonly class UpdateQuestionAnswer
{
    /**
     * Update or provide the answer to a question within the 24h window.
     */
    public function handle(Question $question, string $answer, int $userId): Question
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

        $question->update($attributes);

        // Rewriting an answer that people have already liked drops those
        // likes, the same way editing a question does
        // (UpdateQuestion::handle, $clearLikes). The web's answer editor is
        // Livewire\Questions\Edit::update() -- it validates `answer` and
        // nothing else -- and it passes $originalAnswer !== null as
        // $clearLikes (Edit.php:117), so a like on the old wording was never
        // meant to survive a rewrite. Keying off an answer that already
        // existed, rather than off "the text changed", matters: a first
        // answer is not an edit.
        if ($question->wasChanged('answer') && array_key_exists('answer_updated_at', $attributes)) {
            $question->likes()->delete();
        }

        return (new FeedQuestion)(Question::query()->whereKey($question->id), $userId)->firstOrFail();
    }
}
