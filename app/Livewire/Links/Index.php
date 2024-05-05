<?php

declare(strict_types=1);

namespace App\Livewire\Links;

use App\Jobs\UpdateUserAvatar;
use App\Models\Link;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
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
            dispatch(new UpdateUserAvatar($user));
        }

        $this->dispatch('notification.created', message: 'Link deleted.');
    }

    /**
     * Refresh the component.
     */
    #[On('link.created')]
    #[On('link-settings.updated')]
    public function refresh(): void
    {
        //
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        $user = User::with(['links'])->findOrFail($this->userId);
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
