<?php

declare(strict_types=1);

namespace App\Livewire\Topics;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

final class Index extends Component
{
    /**
     * Search query for topics.
     */
    #[Url]
    public string $search = '';

    /**
     * Toggle follow for a topic.
     */
    public function toggleFollow(int $topicId, #[CurrentUser] ?User $user): void
    {
        if (! $user instanceof User) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $topic = Topic::findOrFail($topicId);

        if ($topic->is_system || ! $topic->is_active) {
            return;
        }

        if ($topic->followers()->where('users.id', $user->id)->exists()) {
            $topic->followers()->detach($user->id);
            $this->dispatch('notification.created', message: "Unfollowed {$topic->name}.");
        } else {
            $topic->followers()->attach($user->id);
            $this->dispatch('notification.created', message: "Following {$topic->name}.");
        }
    }

    /**
     * Render the component.
     */
    public function render(#[CurrentUser] ?User $user): View
    {
        $topics = Topic::query()
            ->where('is_active', true)
            ->where('is_discoverable', true)
            ->where('is_system', false)
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $q): void {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->withCount(['followers', 'questions'])
            ->orderByDesc('followers_count')
            ->orderBy('name')
            ->get();

        $followedTopicIds = $user instanceof User
            ? $user->followedTopics()->pluck('topics.id')->all()
            : [];

        return view('livewire.topics.index', [
            'topics' => $topics,
            'followedTopicIds' => $followedTopicIds,
        ]);
    }
}
