<?php

declare(strict_types=1);

namespace App\Livewire\Links;

use App\Jobs\UpdateUserAvatar;
use App\Models\Link;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Symfony\Component\HttpFoundation\IpUtils;

final class Index extends Component
{
    /**
     * The component's user ID.
     */
    #[Locked]
    public int $userId;

    /**
     * Increment the clicks counter.
     */
    #[Renderless]
    public function click(int $linkId): void
    {
        $ipAddress = type(request()->ip())->asString();
        $cacheKey = IpUtils::anonymize($ipAddress).'-clicked-'.$linkId;

        if (auth()->id() === $this->userId || Cache::has($cacheKey)) {
            return;
        }

        Link::query()
            ->whereKey($linkId)
            ->increment('click_count');

        Cache::put($cacheKey, true, now()->addDay());
    }

    /**
     * Store the new order of the links.
     *
     * @param  array<int, string>  $sort
     */
    public function storeSort(array $sort): void
    {
        $user = type(auth()->user())->as(User::class);

        $sort = collect($sort)
            ->map(fn (string $linkId): ?int => $user->links->contains($linkId) ? ((int) $linkId) : null)
            ->filter()
            ->values()
            ->toArray();

        $user->update([
            'links_sort' => count($sort) === 0 ? null : $sort,
        ]);
    }

    /**
     * Destroy the given link.
     *
     * @throws AuthorizationException
     */
    public function destroy(int $linkId): void
    {
        $user = type(auth()->user())->as(User::class);

        $link = Link::findOrFail($linkId);

        $this->authorize('delete', $link);

        $link->delete();

        if (! $user->is_uploaded_avatar) {
            UpdateUserAvatar::dispatch($user);
        }

        $this->dispatch('notification.created', message: 'Link deleted.');
    }

    /**
     * Set visibility the given link.
     */
    public function setVisibility(int $linkId): void
    {
        $link = Link::findOrFail($linkId);

        $this->authorize('update', $link);

        $link->update([
            'is_visible' => ! $link->is_visible,
        ]);
    }

    /**
     * Follow the given user.
     */
    public function follow(int $targetId): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $user = type(auth()->user())->as(User::class);

        $target = User::findOrFail($targetId);

        $this->authorize('follow', $target);

        if ($target->followers()->where('follower_id', $user->id)->exists()) {
            return;
        }

        $user->following()->attach($targetId);

        $this->dispatch('user.followed');
    }

    /**
     * Unfollow the given user.
     */
    public function unfollow(int $targetId): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $user = type(auth()->user())->as(User::class);

        $target = User::findOrFail($targetId);

        $this->authorize('unfollow', $target);

        if ($target->followers()->where('follower_id', $user->id)->doesntExist()) {
            return;
        }

        $user->following()->detach($targetId);

        $this->dispatch('user.unfollowed');
    }

    /**
     * Refresh the component.
     */
    #[On('link.created')]
    #[On('link.updated')]
    #[On('link-settings.updated')]
    #[On('following.updated')]
    public function refresh(): void
    {
        //
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        $user = User::query()
            ->with(['links' => function (Relation $relation): void {
                $relation->getQuery()
                    ->when(auth()->id() !== $this->userId, function (Builder $query): void {
                        $query->where('is_visible', true);
                    });
            }])
            ->withCount('followers')
            ->withCount('following')
            ->findOrFail($this->userId);
        $sort = $user->links_sort;

        return view('livewire.links.index', [
            'user' => $user,
            'questionsReceivedCount' => $user->questionsReceived()
                ->where('is_reported', false)
                ->where('is_ignored', false)
                ->where('answer', '!=', null)->count(),
            'links' => $user->links->sortBy(function (Link $link) use ($sort): int {
                if (($index = array_search($link->id, $sort)) === false) {
                    return 1_000_000 + $link->id;
                }

                return $index;
            })->values(),
        ]);
    }
}
