<?php

declare(strict_types=1);

namespace App\Livewire\Questions;

use App\Models\Tag;
use App\Models\User;
use App\Rules\NoBlankCharacters;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * @property-read bool $isSharingUpdate
 * @property-read int $maxContentLength
 */
final class Create extends Component
{
    /**
     * The component's user ID.
     */
    #[Locked]
    public int $toId;

    /**
     * The component's content.
     */
    public string $content = '';

    /**
     * The component's anonymously state.
     */
    public bool $anonymously = true;

    /**
     * The component's showTagsDropdown state.
     */
    public bool $showTagsDropdown = false;

    /**
     * The component's tags.
     */
    public array $tags = [];

    /**
     * Tag created by the user.
     */
    public string $customTag = '';

    /**
     * Mount the component.
     */
    public function mount(Request $request): void
    {
        if (auth()->check()) {
            $user = type($request->user())->as(User::class);

            $this->anonymously = $user->prefers_anonymous_questions;
        }
    }

    /**
     * Determine if the user is sharing an update.
     */
    #[Computed]
    public function isSharingUpdate(): bool
    {
        return $this->toId === auth()->id();
    }

    /**
     * Get the maximum content length.
     */
    #[Computed]
    public function maxContentLength(): int
    {
        return $this->isSharingUpdate ? 1000 : 255;
    }

    /**
     * Refresh the component.
     */
    #[On('link-settings.updated')]
    public function refresh(): void
    {
        //
    }

    /**
     * Convert the component's content to the parsed value on update.
     */
    public function updatedContent($value)
    {
        // still has a major bug, it displays styles of the tag to the user
        $this->content = $this->parse_tags($value);
    }

    /**
     * Get the parse content (for debugging).
     */
    #[Computed]
    public function parsedContent()
    {
        return $this->parse_tags($this->content);
    }

    /**
     * Handle the input change.
     */
    #[On('inputChanged')]
    public function handleInputChange(string $input): void
    {
        if (! $this->lastWordStartsWith($input)) {
            $this->showTagsDropdown = false;

            return;
        }

        if (mb_strripos($input, '#') === mb_strlen($input) - 1) {
            $this->showTagsDropdown = true;
            $this->fetchTrendingTags();
        } else {
            $this->showTagsDropdown = false;

            $hashtag = $this->extractHashtag($input);

            if ($hashtag) {
                $this->showTagsDropdown = true;
                $this->fetchTags($hashtag);
            } else {
                $this->showTagsDropdown = false;
            }
        }
    }

    /**
     * Select a tag.
     */
    public function selectTag(string $tagName): void
    {
        $this->content = preg_replace('/#[^#\s]*$/', "#{$tagName}", $this->content);
        $this->showTagsDropdown = false;
        // Dispatch browser event to focus the textarea
        $this->dispatch('tag-selected');
    }

    /**
     * Stores a new question.
     */
    public function store(Request $request): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $user = type($request->user())->as(User::class);

        if (! app()->isLocal() && $user->questionsSent()->where('created_at', '>=', now()->subMinute())->count() >= 3) {
            $this->addError('content', 'You can only send 3 questions per minute.');

            return;
        }

        if (! app()->isLocal() && $user->questionsSent()->where('created_at', '>=', now()->subDay())->count() > 30) {
            $this->addError('content', 'You can only send 30 questions per day.');

            return;
        }

        /** @var array<string, mixed> $validated */
        $validated = $this->validate([
            'anonymously' => ['boolean', Rule::excludeIf($this->isSharingUpdate)],
            'content' => ['required', 'string', 'max:'.$this->maxContentLength, new NoBlankCharacters()],
        ]);

        if ($this->isSharingUpdate) {
            $validated['answer_created_at'] = now();
            $validated['answer'] = $validated['content'];
            $validated['content'] = '__UPDATE__';
        }

        $question = $user->questionsSent()->create([...$validated, 'to_id' => $this->toId]);

        $currentTags = $this->getCurrentTagIds();

        if ($currentTags) {
            $question->tags()->sync($currentTags);
        }

        $this->reset(['content']);

        $this->anonymously = $user->prefers_anonymous_questions;

        $this->dispatch('question.created');
        $this->dispatch('notification.created', message: 'Question sent.');
    }

    public function getCurrentTagIds(): ?array
    {
        $allTagIds = [];

        // Check for any tags in the content
        $checkTags = preg_match_all('/#([^\s,.?!\/@<]+)/i', $this->content, $tagMatches);

        // return null is no tag found
        if (! $checkTags) {
            return null;
        }

        // get unique values
        $uniqueTagNames = array_unique($tagMatches[1]);

        // find tags from the database
        $existingTags = Tag::whereIn('name', $uniqueTagNames)->get();

        // Create new tags if no existing tag found
        if (! $existingTags) {
            foreach ($uniqueTagNames as $tagName) {
                $tag = Tag::create(['name' => $tagName]);
                $allTagIds[] = $tag->id;
            }

            return $allTagIds;
        }

        // Extract existing tag names
        $existingTagNames = $existingTags->pluck('name')->toArray();

        // Determine the new tags that need to be created
        $customTagNames = array_diff($uniqueTagNames, $existingTagNames);

        if (! $customTagNames) {
            $allTagIds = $existingTags->pluck('id')->toArray();

            return $allTagIds;
        }

        // create new tags
        foreach ($customTagNames as $customTagName) {
            $tag = Tag::create(['name' => $customTagName]);
            $allTagIds[] = $tag->id;
        }

        return $allTagIds;
    }

    /**
     * Parse selected tags.
     */
    public function parse_tags(string $content): string
    {
        return preg_replace('/#(\w+)/', '<span class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" wire-navigate>#$1</span>', $content);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        $user = User::findOrFail($this->toId);

        return view('livewire.questions.create', [
            'user' => $user,
        ]);
    }

    /**
     * Extract the latest hashtag from the input.
     */
    protected function extractHashtag(string $input): ?string
    {
        preg_match('/#(\w*)$/', $input, $matches);

        return $matches[1] ?? null;
    }

    /**
     * Fetch tags from the database.
     */
    protected function fetchTags(string $search): void
    {
        $tags = Tag::where('name', 'like', '%'.$search.'%')
            ->get()
            ->toArray();
        if (blank($tags)) {
            $this->customTag = $search;
        }
        $this->tags = $tags;
    }

    /**
     * Fetch tags from the database.
     */
    protected function fetchTrendingTags(): void
    {
        $all_tags = Tag::latest()->limit(20)->get()->toArray();
        if (blank($all_tags)) {
            $this->customTag = '';
        }
        $this->tags = $all_tags;
    }

    /**
     * Check if the last word of a given string starts with a given letter
     */
    protected function lastWordStartsWith($string, $letter = '#')
    {
        //return early if there is no content
        if (! mb_strlen(trim($this->content))) {
            $this->showTagsDropdown = false;

            return false;
        }
        // Trim any trailing spaces from the string
        $trimmedString = trim($string);

        // Split the string into an array of words
        $words = explode(' ', $trimmedString);

        // Get the last word from the array
        $lastWord = end($words);

        // Check if the first character of the last word is $letter
        if (mb_strtolower($lastWord[0]) === $letter) {
            return true;
        }

        return false;

    }
}
