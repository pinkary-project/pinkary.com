<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Collection;

final readonly class Mentions
{
    /**
     * Get the users mentioned in a question.
     */
    public function usersMentioned(Question $question): Collection
    {
        return collect($this->mentionsFromQuestion($question))
            ->each(fn (string $username) => User::whereUsername($username)->first())
            ->filter(fn (?User $user): bool => $user instanceof User);
    }

    /**
     * Get the unique mentions from a question.
     */
    private function mentionsFromQuestion(Question $question): array
    {
        return array_unique(array_merge(
            $this->mentionsFromContent($question->content),
            $this->mentionsFromContent($question->answer ?? ''),
        ));
    }

    /**
     * Get the mentions from a text.
     */
    private function mentionsFromContent(string $content): array
    {
        preg_match_all("/\@(\w+)/", $content, $matches);

        return $matches[1];
    }
}
