<?php

declare(strict_types=1);

namespace App\EventActions;

use App\Models\Hashtag;
use App\Models\Question;
use Illuminate\Support\Collection;

final readonly class UpdateQuestionHashtags
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public Question $question,
    ) {
        //
    }

    /**
     * @return array{attached: array<int, int>, detached: array<int, int>, updated: array<int, int>}
     */
    public function handle(): array
    {
        $parsedHashtags = $this->parsedHashtagNames();

        $existingHashtags = Hashtag::query()->whereIn('name', $parsedHashtags->all())->get();

        $newHashtags = $parsedHashtags->diff($existingHashtags->pluck('name'))
            ->map(fn (string $name): Hashtag => Hashtag::query()->create(['name' => $name]));

        return $this->question->hashtags()->sync($existingHashtags->merge($newHashtags));
    }

    /**
     * Get the unique hashtag names found in the question.
     *
     * @return Collection<int, non-falsy-string>
     */
    private function parsedHashtagNames(): Collection
    {
        $matches = [];

        preg_match_all(
            '/(<(a|code|pre)\s+[^>]*>.*?<\/\2>)|(?<!&)#([a-z0-9_]+)/is',
            "{$this->question->answer} {$this->question->content}",
            $matches,
        );

        return collect($matches[3] ?? [])
            ->filter()
            ->unique()
            ->values();
    }
}
