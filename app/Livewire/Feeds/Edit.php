<?php

declare(strict_types=1);

namespace App\Livewire\Feeds;

use App\Enums\FeedVisibility;
use App\Livewire\Concerns\NeedsVerifiedEmail;
use App\Models\Feed;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class Edit extends Component
{
    use NeedsVerifiedEmail;

    /**
     * The feed ID.
     */
    #[Locked]
    public int $feedId;

    /**
     * Feed name.
     */
    public string $name = '';

    /**
     * Feed description.
     */
    public string $description = '';

    /**
     * Feed visibility.
     */
    public string $visibility = 'public';

    /**
     * Selected topic IDs.
     *
     * @var array<int, int>
     */
    public array $selectedTopics = [];

    /**
     * Selected user IDs.
     *
     * @var array<int, int>
     */
    public array $selectedPeople = [];

    /**
     * Search string for topics.
     */
    public string $topicSearch = '';

    /**
     * Search string for people.
     */
    public string $peopleSearch = '';

    /**
     * Mount the component.
     */
    public function mount(Feed $feed): void
    {
        $this->feedId = $feed->id;
        $this->name = $feed->name;
        $this->description = $feed->description ?? '';
        $this->visibility = $feed->visibility->value;
        $this->selectedTopics = $feed->topics()->pluck('topics.id')->all();
        $this->selectedPeople = $feed->people()->pluck('users.id')->all();
    }

    /**
     * Add a topic to selection.
     */
    public function addTopic(int $id): void
    {
        if (! in_array($id, $this->selectedTopics, true)) {
            $this->selectedTopics[] = $id;
        }
        $this->topicSearch = '';
        $this->resetErrorBag('membership');
    }

    /**
     * Add or create a topic by name on the fly.
     */
    public function addOrCreateTopic(string $name): void
    {
        $clean = mb_ltrim(mb_trim($name), '#');

        if (mb_strlen($clean) < 2 || mb_strlen($clean) > 50) {
            return;
        }

        $topic = Topic::firstOrCreate(
            ['slug' => Str::slug($clean)],
            [
                'name' => $clean,
                'is_active' => true,
                'is_discoverable' => true,
                'is_system' => false,
            ]
        );

        $this->addTopic($topic->id);
    }

    /**
     * Remove a topic from selection.
     */
    public function removeTopic(int $id): void
    {
        $this->selectedTopics = array_values(array_filter(
            $this->selectedTopics,
            fn (int $topicId): bool => $topicId !== $id,
        ));
    }

    /**
     * Add a user to selection.
     */
    public function addPerson(int $id): void
    {
        if (! in_array($id, $this->selectedPeople, true)) {
            $this->selectedPeople[] = $id;
        }
        $this->peopleSearch = '';
        $this->resetErrorBag('membership');
    }

    /**
     * Remove a user from selection.
     */
    public function removePerson(int $id): void
    {
        $this->selectedPeople = array_values(array_filter(
            $this->selectedPeople,
            fn (int $userId): bool => $userId !== $id,
        ));
    }

    /**
     * Update the feed.
     */
    public function update(#[CurrentUser] User $user): void
    {
        if ($this->doesNotHaveVerifiedEmail()) {
            return;
        }

        $feed = Feed::findOrFail($this->feedId);

        if ($feed->user_id !== $user->id) {
            abort(403);
        }

        if (empty($this->selectedTopics) && empty($this->selectedPeople)) {
            $this->addError('membership', 'A custom feed must contain at least one Topic or Person.');

            return;
        }

        /** @var array<string, mixed> $validated */
        $validated = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:60'],
            'description' => ['nullable', 'string', 'max:255'],
            'visibility' => ['required', Rule::enum(FeedVisibility::class)],
            'selectedTopics' => ['array'],
            'selectedTopics.*' => ['integer', Rule::exists('topics', 'id')->where('is_active', true)],
            'selectedPeople' => ['array'],
            'selectedPeople.*' => ['integer', Rule::exists('users', 'id')],
        ]);

        $feed->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'visibility' => $validated['visibility'],
        ]);

        $feed->topics()->sync($this->selectedTopics);
        $feed->people()->sync($this->selectedPeople);

        $this->dispatch('notification.created', message: "Feed '{$feed->name}' updated.");
        $this->redirectRoute('feeds.show', ['feed' => $feed->id], navigate: true);
    }

    /**
     * Delete the feed.
     */
    public function delete(#[CurrentUser] User $user): void
    {
        if ($this->doesNotHaveVerifiedEmail()) {
            return;
        }

        $feed = Feed::findOrFail($this->feedId);

        if ($feed->user_id !== $user->id) {
            abort(403);
        }

        $feed->topics()->detach();
        $feed->people()->detach();
        $feed->followers()->detach();
        $feed->delete();

        $this->dispatch('notification.created', message: 'Feed deleted.');
        $this->redirectRoute('feeds.index', navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        $feed = Feed::findOrFail($this->feedId);

        $searchedTopics = [];
        if ($this->topicSearch !== '') {
            $searchedTopics = Topic::query()
                ->where('is_active', true)
                ->where('is_system', false)
                ->whereNotIn('id', $this->selectedTopics)
                ->where('name', 'like', "%{$this->topicSearch}%")
                ->take(8)
                ->get();
        }

        $searchedPeople = [];
        if ($this->peopleSearch !== '') {
            $searchedPeople = User::query()
                ->whereNotIn('id', $this->selectedPeople)
                ->where(function (Builder $query): void {
                    $query->where('name', 'like', "%{$this->peopleSearch}%")
                        ->orWhere('username', 'like', "%{$this->peopleSearch}%");
                })
                ->take(8)
                ->get();
        }

        $chosenTopics = empty($this->selectedTopics)
            ? collect()
            : Topic::whereIn('id', $this->selectedTopics)->get();

        $chosenPeople = empty($this->selectedPeople)
            ? collect()
            : User::whereIn('id', $this->selectedPeople)->get();

        return view('livewire.feeds.edit', [
            'feed' => $feed,
            'searchedTopics' => $searchedTopics,
            'searchedPeople' => $searchedPeople,
            'chosenTopics' => $chosenTopics,
            'chosenPeople' => $chosenPeople,
        ]);
    }
}
