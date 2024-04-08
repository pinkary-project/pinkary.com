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
     *
     * @return Collection<string, User>
     */
    public function usersMentioned(Question $question): Collection
    {
        return collect($this->mentionsFromQuestion($question))
            ->map(fn (string $username) => User::whereUsername($username)->first())
            ->filter(fn (?User $user): bool => $user instanceof User);
    }

    /**
     * Get the unique mentions from a question.
     *
     * @return array<string>
     */
    private function mentionsFromQuestion(Question $question): array
    {
        return array_unique(array_merge(
            $this->mentionsFromContent(type($question->content)->asString()),
            $this->mentionsFromContent(type($question->answer)->asString()),
        ));
    }

    /**
     * Get the mentions from a text.
     *
     * @return array<string>
     */
    private function mentionsFromContent(string $content): array
    {
        preg_match_all("/\@(\w+)/", $content, $matches);

        return $matches[1];
    }
}
