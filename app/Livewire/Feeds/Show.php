<?php

declare(strict_types=1);

namespace App\Livewire\Feeds;

use App\Livewire\Concerns\HasLoadMore;
use App\Models\Feed;
use App\Models\Question;
use App\Models\User;
use App\Queries\Feeds\CustomQuestionsFeed;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

final class Show extends Component
{
    use HasLoadMore;

    /**
     * The feed ID.
     */
    #[Locked]
    public int $feedId;

    /**
     * Mount the component.
     */
    public function mount(Feed $feed): void
    {
        $this->feedId = $feed->id;
    }

    /**
     * Toggle follow for this feed.
     */
    public function toggleFollow(#[CurrentUser] ?User $user): void
    {
        if (! $user instanceof User) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $feed = Feed::findOrFail($this->feedId);

        if (! $feed->isPublic() || $feed->user_id === $user->id) {
            return;
        }

        if ($feed->followers()->where('users.id', $user->id)->exists()) {
            $feed->followers()->detach($user->id);
            $this->dispatch('notification.created', message: "Unfollowed {$feed->name}.");
        } else {
            $feed->followers()->attach($user->id);
            $this->dispatch('notification.created', message: "Following {$feed->name}.");
        }
    }

    /**
     * Ignore the given question.
     */
    #[On('question.ignore')]
    public function ignore(string $questionId): void
    {
        $question = Question::findOrFail($questionId);

        $this->authorize('ignore', $question);

        $question->update(['is_ignored' => true]);

        $this->dispatch('question.ignored');
    }

    /**
     * Refresh the feed.
     */
    #[On('question.created')]
    #[On('question.updated')]
    public function refresh(): void {}

    /**
     * Render the component.
     */
    public function render(#[CurrentUser] ?User $user): View
    {
        $feed = Feed::with(['user', 'topics', 'people'])
            ->withCount('followers')
            ->findOrFail($this->feedId);

        if (! $feed->isPublic() && $feed->user_id !== $user?->id) {
            abort(403);
        }

        $questions = (new CustomQuestionsFeed($feed))
            ->builder()
            ->simplePaginate($this->perPage);

        $isFollowed = $user instanceof User && $feed->isFollowedBy($user);

        return view('livewire.feeds.show', [
            'feed' => $feed,
            'questions' => $questions,
            'isFollowed' => $isFollowed,
        ]);
    }
}
