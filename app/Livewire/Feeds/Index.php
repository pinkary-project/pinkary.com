<?php

declare(strict_types=1);

namespace App\Livewire\Feeds;

use App\Enums\FeedVisibility;
use App\Models\Feed;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\View\View;
use Livewire\Component;

final class Index extends Component
{
    /**
     * Toggle follow for a feed.
     */
    public function toggleFollow(int $feedId, #[CurrentUser] ?User $user): void
    {
        if (! $user instanceof User) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $feed = Feed::findOrFail($feedId);

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
     * Render the component.
     */
    public function render(#[CurrentUser] ?User $user): View
    {
        $myFeeds = $user instanceof User
            ? $user->createdFeeds()
                ->with(['topics', 'people'])
                ->withCount('followers')
                ->latest()
                ->get()
            : collect();

        $followedFeeds = $user instanceof User
            ? $user->followedFeeds()
                ->with(['user', 'topics', 'people'])
                ->withCount('followers')
                ->latest()
                ->get()
            : collect();

        $discoverFeeds = Feed::query()
            ->where('visibility', FeedVisibility::Public)
            ->when($user instanceof User, function ($query) use ($user): void {
                $query->where('user_id', '!=', $user->id)
                    ->whereNotIn('id', $user->followedFeeds()->pluck('feeds.id'));
            })
            ->with(['user', 'topics', 'people'])
            ->withCount('followers')
            ->orderByDesc('followers_count')
            ->take(10)
            ->get();

        return view('livewire.feeds.index', [
            'myFeeds' => $myFeeds,
            'followedFeeds' => $followedFeeds,
            'discoverFeeds' => $discoverFeeds,
        ]);
    }
}
