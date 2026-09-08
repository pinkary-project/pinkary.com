<?php

declare(strict_types=1);

namespace App\Livewire\Links;

use App\Actions\Links\DeleteLink;
use App\Actions\Links\UpdateLinkClicks;
use App\Actions\Links\UpdateLinkOrder;
use App\Actions\Links\UpdateLinkVisibility;
use App\Actions\Users\CreateFollow;
use App\Actions\Users\DeleteFollow;
use App\Models\Link;
use App\Models\Scopes\WhereNotModerated;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Container\Attributes\CurrentUser;
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
    public function click(UpdateLinkClicks $updateLinkClicks, int $linkId): void
    {
        $ipAddress = (string) request()->ip();
        $cacheKey = IpUtils::anonymize($ipAddress).'-clicked-'.$linkId;

        if (auth()->id() === $this->userId || Cache::has($cacheKey)) {
            return;
        }

        $updateLinkClicks->handle($linkId, $cacheKey);
    }

    /**
     * Store the new order of the links.
     *
     * @param  array<int, string>  $sort
     */
    public function storeSort(array $sort, #[CurrentUser] User $user, UpdateLinkOrder $updateLinkOrder): void
    {

        $updateLinkOrder->handle($user, $sort);
    }

    /**
     * Destroy the given link.
     *
     * @throws AuthorizationException
     */
    public function destroy(int $linkId, #[CurrentUser] User $user, DeleteLink $deleteLink): void
    {

        $link = Link::findOrFail($linkId);

        $this->authorize('delete', $link);

        $deleteLink->handle($user, $link);

        $this->dispatch('close-modal', 'delete-link');
        $this->dispatch('notification.created', message: 'Link deleted.');
    }

    /**
     * Set visibility the given link.
     */
    public function setVisibility(UpdateLinkVisibility $updateLinkVisibility, int $linkId): void
    {
        $link = Link::findOrFail($linkId);

        $this->authorize('update', $link);

        $updateLinkVisibility->handle($link);
    }

    /**
     * Follow the given user.
     */
    public function follow(int $targetId, #[CurrentUser] ?User $user, CreateFollow $createFollow): void
    {
        if (! $user instanceof User) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $target = User::findOrFail($targetId);

        $this->authorize('follow', $target);

        if ($target->followers()->where('follower_id', $user->id)->exists()) {
            return;
        }

        $createFollow->handle($user, $targetId);

        $this->dispatch('user.followed');
    }

    /**
     * Unfollow the given user.
     */
    public function unfollow(int $targetId, #[CurrentUser] ?User $user, DeleteFollow $deleteFollow): void
    {
        if (! $user instanceof User) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $target = User::findOrFail($targetId);

        $this->authorize('unfollow', $target);

        if ($target->followers()->where('follower_id', $user->id)->doesntExist()) {
            return;
        }

        $deleteFollow->handle($user, $targetId);

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
                ->tap(new WhereNotModerated)
                ->where('answer', '!=', '')->count(),
            'links' => $user->links->sortBy(function (Link $link) use ($sort): int {
                if (($index = array_search($link->id, $sort)) === false) {
                    return 1_000_000 + $link->id;
                }

                return $index;
            })->values(),
        ]);
    }
}
