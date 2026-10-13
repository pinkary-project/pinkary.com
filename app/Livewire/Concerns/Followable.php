<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Actions\Users\CreateFollow;
use App\Actions\Users\DeleteFollow;
use App\Models\User;
use Livewire\Attributes\Renderless;

trait Followable
{
    /**
     * Follows the given user.
     */
    #[Renderless]
    public function follow(CreateFollow $createFollow, int $id): void
    {
        $user = auth()->user();

        if ($user === null) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $createFollow->handle($user, $id);

        if ($this->shouldHandleFollowingCount()) {
            $this->dispatch('following.updated');
        }

        $this->dispatch('user.followed', id: $id);
    }

    /**
     * Unfollows the given user.
     */
    #[Renderless]
    public function unfollow(DeleteFollow $deleteFollow, int $id): void
    {
        $user = auth()->user();

        if ($user === null) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $deleteFollow->handle($user, $id);

        if ($this->shouldHandleFollowingCount()) {
            $this->dispatch('following.updated');
        }

        $this->dispatch('user.unfollowed', id: $id);
    }

    /**
     * Indicates if the following count should be handled.
     */
    protected function shouldHandleFollowingCount(): bool
    {
        return false;
    }
}
